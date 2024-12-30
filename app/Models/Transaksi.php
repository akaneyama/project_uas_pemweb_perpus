<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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


}
