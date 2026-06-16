<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\{Pesanan, Pembayaran, Setting};
use App\Mail\ReceiptMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Ambil pengaturan Midtrans dari database (atau fallback ke config jika belum diatur)
        \Midtrans\Config::$serverKey = Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey'));
        \Midtrans\Config::$isProduction = Setting::getVal('midtrans_is_production', config('services.midtrans.isProduction')) == '1';
        \Midtrans\Config::$isSanitized = config('services.midtrans.isSanitized');
        \Midtrans\Config::$is3ds = config('services.midtrans.is3ds');
    }

    public function checkout($id_pesanan)
    {
        $pesanan = Pesanan::with(['detail_pesanan.menu', 'pembayaran'])->findOrFail($id_pesanan);
        $pembayaran = $pesanan->pembayaran;

        // FIX #8: Cek ownership — pastikan pesanan milik konsumen yang login
        if ($pesanan->id_konsumen !== auth()->id()) {
            abort(403, 'Anda tidak berhak mengakses pesanan ini.');
        }

        // Cegah generate ulang jika sudah lunas
        if ($pembayaran->status === 'paid') {
            return redirect()->back()->with('error', 'Pesanan ini sudah lunas.');
        }

        // Midtrans di-disable sesuai permintaan, langsung return ke view checkout manual


        return view('konsumen.checkout', compact('pesanan', 'pembayaran'));
    }

    public function verifyAi(Request $request, $id_pesanan)
    {
        $pesanan = Pesanan::with('pembayaran')->findOrFail($id_pesanan);
        $pembayaran = $pesanan->pembayaran;

        // FIX #7: Cek ownership — cegah konsumen lain memverifikasi pesanan orang lain
        if ($pesanan->id_konsumen !== auth()->id()) {
            return response()->json(['error' => 'Anda tidak berhak memverifikasi pesanan ini.'], 403);
        }

        if ($pembayaran->status === 'paid') {
            return response()->json(['error' => 'Pesanan sudah lunas.'], 400);
        }

        $request->validate([
            'receipt_image' => 'required|image|max:5120' // max 5MB
        ]);

        $apiKey = Setting::getVal('gemini_api_key');
        if (!$apiKey) {
            return response()->json(['error' => 'AI Verification belum dikonfigurasi oleh Admin.'], 500);
        }

        try {
            $image = $request->file('receipt_image');
            $base64Image = base64_encode(file_get_contents($image->getRealPath()));
            $mimeType = $image->getMimeType();

            $prompt = "Ini adalah gambar bukti transfer pembayaran. Tolong analisis gambar ini dan pastikan apakah transaksi ini berhasil. Kembalikan data dalam format JSON murni dengan dua key: 'status' (bernilai 'success' jika transaksi berhasil, atau 'failed' jika gagal/tidak jelas), dan 'amount' (berisi angka nominal uang yang ditransfer, tanpa titik/koma/Rp).";
            $apiKey = trim(\App\Models\Setting::getVal('gemini_api_key'));

            $payload = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt],
                            [
                                'inlineData' => [
                                    'mimeType' => $mimeType,
                                    'data' => $base64Image
                                ]
                            ]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'responseMimeType' => 'application/json',
                ]
            ];

            $response = Http::post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:generateContent?key=" . $apiKey, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $textResponse = $data['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
                
                $textResponse = str_replace(['```json', '```'], '', $textResponse);
                $result = json_decode(trim($textResponse), true);

                if ($result && isset($result['status']) && isset($result['amount'])) {
                    if ($result['status'] === 'success') {
                        $amount = (int) $result['amount'];
                        
                        if ($amount >= $pembayaran->total_bayar) {
                            $pembayaran->update([
                                'status' => 'paid',
                                'metode' => 'qris',
                                'tanggal' => now()
                            ]);
                            $pesanan->update(['status' => 'processing']);
                            
                            \App\Models\Notification::create([
                                'type' => 'new_order',
                                'message' => 'Pesanan (QRIS Verified): Order #' . $pesanan->id . ' telah diverifikasi sistem.',
                                'is_read' => false
                            ]);

                            // Notify Customer
                            if ($pesanan->konsumen) {
                                $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                                    'Pembayaran Berhasil!',
                                    'Pembayaran untuk Order #' . $pesanan->id . ' telah diverifikasi. Pesanan sedang diproses.',
                                    '/konsumen/profil'
                                ));
                            }

                            // Trigger Push Notification to Admin and Kasir
                            $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->get();
                            \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
                                'Pesanan Dibayar',
                                'Order #' . $pesanan->id . ' telah diverifikasi pembayarannya secara otomatis.',
                                '/kasir/pesanan-aktif'
                            ));

                            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil diverifikasi! Pesanan Anda sedang diproses.']);
                        } else {
                            return response()->json(['error' => 'Nominal transfer (Rp ' . number_format($amount, 0, ',', '.') . ') kurang dari total tagihan (Rp ' . number_format($pembayaran->total_bayar, 0, ',', '.') . '). Mohon periksa kembali.']);
                        }
                    } else {
                        return response()->json(['error' => 'Sistem tidak dapat memastikan transaksi berhasil. Silakan tunjukkan bukti ke kasir manual.']);
                    }
                } else {
                    return response()->json(['error' => 'Validasi gagal. Harap tunjukkan struk ke kasir.']);
                }
            } else {
                Log::error('Gemini API Error: ' . $response->body());
                return response()->json(['error' => 'Layanan verifikasi sedang sibuk.'], 500);
            }

        } catch (\Exception $e) {
            Log::error('AI Verify Exception: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem saat memproses gambar.'], 500);
        }
    }

    public function webhook(Request $request)
    {
        // 1. Validasi Signature Key (Wajib untuk Keamanan Production)
        $serverKey = Setting::getVal('midtrans_server_key', config('services.midtrans.serverKey'));
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);
        
        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        // 2. Ekstrak ID Pesanan dari order_id (ORDER-{id}-{time})
        $orderIdParts = explode('-', $request->order_id);
        $id_pesanan = $orderIdParts[1];

        $pembayaran = Pembayaran::where('id_pesanan', $id_pesanan)->first();
        if (!$pembayaran) return response()->json(['message' => 'Not Found'], 404);

        // 3. Update Status Berdasarkan Midtrans
        $transactionStatus = $request->transaction_status;

        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            $pembayaran->update([
                'status' => 'paid',
                'metode' => $request->payment_type == 'qris' ? 'qris' : 'cash', // Sesuaikan
                'tanggal' => now()
            ]);
            
            
            // Opsional: Update status pesanan langsung ke 'processing'
            Pesanan::where('id', $id_pesanan)->update(['status' => 'processing']);

            // Beri notifikasi ke kasir
            \App\Models\Notification::create([
                'type' => 'new_order',
                'message' => 'Pesanan Baru (Midtrans): Order #' . $id_pesanan . ' telah LUNAS.',
                'is_read' => false
            ]);

            // Notify Admin and Kasir via Web Push
            $adminsAndKasirs = \App\Models\User::role(['pemilik', 'kasir'])->get();
            \Illuminate\Support\Facades\Notification::send($adminsAndKasirs, new \App\Notifications\WebPushNotification(
                'Pesanan Lunas (Midtrans)',
                'Order #' . $id_pesanan . ' telah dibayar lunas via Midtrans.',
                '/kasir/pesanan-aktif'
            ));

            $pesanan = Pesanan::with('konsumen')->find($id_pesanan);
            if ($pesanan && $pesanan->konsumen) {
                // Notify Customer via Web Push
                $pesanan->konsumen->notify(new \App\Notifications\WebPushNotification(
                    'Pembayaran Berhasil!',
                    'Pembayaran untuk Order #' . $pesanan->id . ' telah berhasil.',
                    '/konsumen/profil'
                ));
            }
            if ($pesanan && $pesanan->konsumen && $pesanan->konsumen->email) {
                try {
                    Mail::to($pesanan->konsumen->email)->send(new ReceiptMail($pesanan));
                } catch (\Exception $mailEx) {
                    Log::error("Gagal mengirim e-receipt Midtrans: " . $mailEx->getMessage());
                }
            }
            
        } elseif (in_array($transactionStatus, ['cancel', 'deny', 'expire'])) {
            // Mengembalikan stok karena pembayaran gagal/batal
            $pesanan = Pesanan::find($id_pesanan);
            if ($pesanan) {
                $pesanan->cancelOrder();
            }
            Log::info("Pembayaran gagal untuk Order ID: {$id_pesanan}. Stok telah dikembalikan.");
        }

        return response()->json(['message' => 'Webhook Berhasil Diterima']);
    }
}