<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PengajuanIzin_Document extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'pengajuanizin_document';
    protected $connection = 'mysql2';
    public $timestamps = false;

    protected $fillable = [
        'id_pengajuanizin',
        'data_verifikasi',
    ];
    public function pengajuanizin()
    {
        return $this->belongsTo(pengajuanizin::class, 'id');
    }
}
