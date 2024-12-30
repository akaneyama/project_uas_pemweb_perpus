<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
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
    public function user(){
        return $this->belongsTo(User::class,'id','id');
    }
    public function buku(){
        return $this->belongsTo(Buku::class,'id_buku','id_buku');
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

            // Kurangi jumlah buku
            $buku->jumlah_buku -= $transaksi->jumlah_peminjaman;
            $buku->save();
        });

        static::deleting(function ($transaksi) {
            $buku = Buku::find($transaksi->id_buku);
            if ($buku) {
                // Kembalikan jumlah buku
                $buku->jumlah_buku += $transaksi->jumlah_peminjaman;
                $buku->save();
            }
        });
    }

}
