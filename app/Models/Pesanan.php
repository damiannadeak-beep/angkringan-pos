<?php 
 
namespace App\Models; 
 
use Illuminate\Database\Eloquent\Model; 
use App\Models\DetailPesanan; 
use App\Models\Pembayaran; 
use App\Models\User; 
use App\Models\Meja; 
 
class Pesanan extends Model 
{ 
    protected $table = 'pesanan'; 
    protected $guarded = []; 

    public function detail_pesanan() 
    { 
        return $this->hasMany(DetailPesanan::class, 'id_pesanan'); 
    } 

    public function pembayaran() 
    { 
        return $this->hasOne(Pembayaran::class, 'id_pesanan'); 
    } 

    public function konsumen() 
    { 
        return $this->belongsTo(User::class, 'id_konsumen'); 
    } 

    public function kasir() 
    { 
        return $this->belongsTo(User::class, 'id_kasir'); 
    } 

    public function meja() 
    { 
        return $this->belongsTo(Meja::class, 'id_meja'); 
    } 

    public function rating()
    {
        return $this->hasOne(Rating::class, 'id_pesanan');
    }

    /**
     * Mengembalikan stok menu dan bahan baku yang sudah terpotong.
     */
    public function restoreStock()
    {
        foreach ($this->detail_pesanan as $detail) {
            $menu = $detail->menu;
            if ($menu) {
                // Kembalikan stok produk jadi/menu
                $menu->increment('stok', $detail->jumlah);
                
                // Kembalikan stok bahan baku yang terikat dengan menu
                foreach ($menu->bahans as $bahan) {
                    $dibutuhkan = $bahan->pivot->jumlah_dibutuhkan * $detail->jumlah;
                    $bahan->increment('stok', $dibutuhkan);
                }
            }
        }
    }

    /**
     * Membatalkan pesanan: set status cancelled dan kembalikan stok.
     */
    public function cancelOrder()
    {
        if ($this->status !== 'cancelled') {
            $this->update(['status' => 'cancelled']);
            $this->restoreStock();
            
            // Opsional: jika mau, ubah status pembayaran jadi failed/cancelled juga
            if ($this->pembayaran) {
                $this->pembayaran->update(['status' => 'failed']);
            }
        }
    }
}
