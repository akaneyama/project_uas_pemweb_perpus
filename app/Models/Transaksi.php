<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Http;
use Filament\Notifications\Notification;

class Transaksi extends Model
{
    use HasFactory;

    protected $table = 'transaksi';
    protected $primaryKey = 'id_trans';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'id_buku',
        'jumlah_peminjaman',
        'tanggal_transaksi',
        'tanggal_pengembalian',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id', 'id');
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class, 'id_buku', 'id_buku');
    }

    public function kembalikanBuku()
    {
        $buku = $this->buku;
        $jumlahPeminjaman = $this->jumlah_peminjaman;

        if (!$buku) {
            Notification::make()
                ->title('Kesalahan')
                ->body('Data buku tidak ditemukan.')
                ->danger()
                ->send();
            return;
        }

        if ($this->status === 'DIKEMBALIKAN') {
            $buku->jumlah_buku += $jumlahPeminjaman;
            $buku->save();

            Notification::make()
                ->title('Buku Dikembalikan')
                ->body('Jumlah buku yang dipinjam telah dikembalikan dan stok diperbarui.')
                ->success()
                ->send();
        }
    }

    protected static function booted()
    {
        static::saved(function ($transaksi) {


            $user = $transaksi->user;
            $buku = $transaksi->buku;

            // Persiapkan data untuk dikirimkan ke API

                $dataToSend = [
                    'nama' => $user->name,
                    'chatId' => $user->number_phone,
                    'buku' => $buku->nama_buku,
                    'jumlah_buku' => $transaksi->jumlah_peminjaman,
                    'status_peminjaman' => $transaksi->status,
                    'id_trans' => $transaksi->id_trans,
                    'tanggal_pengembalian' => $transaksi->tanggal_pengembalian

                ];

            // Kirim data ke API
            try {
                $response = Http::post('http://localhost:3000/api/kirimpesanlaravel', $dataToSend);

                // Cek apakah request berhasil
                if ($response->successful()) {
                    Notification::make()
                        ->title('Berhasil Mengirim Bukti Invoice')
                        ->body('Silahkan cek Whatsapp kalian')
                        //->title('API Request Success')
                        //->body('Data berhasil dikirim ke API.')
                        ->success()
                        ->send();
                } else {
                    Notification::make()
                    ->title('API Request Failed')
                    ->body('Terjadi kesalahan saat mengirim data ke API: ' . $response->body() )
                        ->danger()
                        ->send();
                }
            } catch (\Exception $e) {
                Notification::make()
                    ->title('Error')
                    ->body('Terjadi kesalahan: ' . $e->getMessage())
                    ->danger()
                    ->send();
            }
        });

        static::creating(function ($transaksi) {
            $buku = Buku::find($transaksi->id_buku);

            if (!$buku || $buku->jumlah_buku < $transaksi->jumlah_peminjaman) {
                throw ValidationException::withMessages([
                    'jumlah_peminjaman' => 'Jumlah buku yang tersedia tidak mencukupi.',
                ]);
            }


            $buku->jumlah_buku -= $transaksi->jumlah_peminjaman;
            $buku->save();
        });

        static::updated(function ($transaksi) {

            if ($transaksi->wasChanged('status') && $transaksi->status === 'DIKEMBALIKAN') {
                $transaksi->kembalikanBuku();
            }
        });

        static::deleting(function ($transaksi) {
            if($transaksi->status !== 'DIKEMBALIKAN'){
                $buku = Buku::find($transaksi->id_buku);

                if ($buku) {

                    $buku->jumlah_buku += $transaksi->jumlah_peminjaman;
                    $buku->save();
                }

            }
            return null;

        });
    }
}
