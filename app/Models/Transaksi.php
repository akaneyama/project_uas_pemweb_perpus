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
        static::creating(function ($transaksi) {
            $buku = Buku::find($transaksi->id_buku);

            if (!$buku || $buku->jumlah_buku < $transaksi->jumlah_peminjaman) {
                throw ValidationException::withMessages([
                    'jumlah_peminjaman' => 'Jumlah buku yang tersedia tidak mencukupi.',
                ]);
            }

            // Kurangi jumlah buku saat transaksi dibuat
            $buku->jumlah_buku -= $transaksi->jumlah_peminjaman;
            $buku->save();
        });

        static::updated(function ($transaksi) {
            // Tangani perubahan status menjadi DIKEMBALIKAN
            if ($transaksi->wasChanged('status') && $transaksi->status === 'DIKEMBALIKAN') {
                $transaksi->kembalikanBuku();
            }
        });

        static::deleting(function ($transaksi) {
            $buku = Buku::find($transaksi->id_buku);

            if ($buku) {
                // Kembalikan jumlah buku saat transaksi dihapus
                $buku->jumlah_buku += $transaksi->jumlah_peminjaman;
                $buku->save();
            }
        });
    }
}
