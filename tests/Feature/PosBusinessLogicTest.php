<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Menu;
use App\Models\Bahan;
use App\Models\Pesanan;
use App\Models\Meja;

class PosBusinessLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles karena RefreshDatabase menghapus semua tabel termasuk roles
        \Spatie\Permission\Models\Role::create(['name' => 'kasir', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'pemilik', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::create(['name' => 'konsumen', 'guard_name' => 'web']);

        // Bypass EnsureShiftOpen middleware dalam test karena tidak ada shift aktif
        $this->withoutMiddleware(\App\Http\Middleware\EnsureShiftOpen::class);
    }

    public function test_stok_bahan_berkurang_saat_pesanan_dibuat()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $bahan = Bahan::create([
            'nama_bahan' => 'Kopi',
            'satuan' => 'gram',
            'stok' => 1000,
            'harga_beli' => 50000
        ]);

        $menu = Menu::create([
            'nama_menu' => 'Kopi Hitam',
            'harga' => 10000,
            'stok' => 100,
            'kategori' => 'minuman',
            'is_available' => true
        ]);

        $menu->bahans()->attach($bahan->id, ['jumlah_dibutuhkan' => 20]);

        $meja = Meja::create([
            'nama_meja_atau_nomor' => '1',
            'is_available' => true
        ]);

        $response = $this->actingAs($kasir)->postJson('/kasir/manual-order', [
            'id_meja' => $meja->id,
            'tipe_pesanan' => 'dine_in',
            'pembayaran_langsung' => false,
            'items' => [
                [
                    'id_menu' => $menu->id,
                    'jumlah' => 2
                ]
            ]
        ]);

        $response->assertStatus(200);

        // Stok Kopi = 1000 - (2 * 20) = 960
        $this->assertDatabaseHas('bahans', [
            'id' => $bahan->id,
            'stok' => 960
        ]);
    }

    public function test_pesanan_bisa_divoid_dan_stok_kembali()
    {
        $kasir = User::factory()->create();
        $kasir->assignRole('kasir');

        $bahan = Bahan::create([
            'nama_bahan' => 'Gula',
            'satuan' => 'gram',
            'stok' => 500,
            'harga_beli' => 20000
        ]);

        $menu = Menu::create([
            'nama_menu' => 'Teh Manis',
            'harga' => 5000,
            'stok' => 100,
            'kategori' => 'minuman',
            'is_available' => true
        ]);

        $menu->bahans()->attach($bahan->id, ['jumlah_dibutuhkan' => 15]);

        $pesanan = Pesanan::create([
            'id_kasir' => $kasir->id,
            'tipe_pesanan' => 'takeaway',
            'status' => 'pending',
            'total' => 5000,
            'total_hpp' => 0,
            'tanggal' => now()
        ]);

        $pesanan->detail_pesanan()->create([
            'id_menu' => $menu->id,
            'jumlah' => 1,
            'subtotal' => 5000
        ]);

        // Simulasikan stok sudah berkurang
        $bahan->stok = 485;
        $bahan->save();

        $response = $this->actingAs($kasir)->putJson("/kasir/order/{$pesanan->id}/void", [
            'alasan' => 'Salah input',
            'password' => 'password' // password default user factory
        ]);

        if ($response->status() !== 200) {
            dump($response->json());
        }
        $response->assertStatus(200);

        // Stok kembali ke 500
        $this->assertDatabaseHas('bahans', [
            'id' => $bahan->id,
            'stok' => 500
        ]);

        $this->assertSoftDeleted('pesanan', [
            'id' => $pesanan->id
        ]);
    }
}
