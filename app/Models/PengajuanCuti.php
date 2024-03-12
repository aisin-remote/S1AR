<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PengajuanCuti extends Model
{
    use HasFactory;
    use HasUuids;

    protected $table = 'pengajuancuti';
    public $timestamps = false;
    protected $connection = 'mysql2';

    protected $fillable = [
        'kodepengajuan',
        'empno',
        'tgl_mulai',
        'tgl_selesai',
        'tgl_pengajuan',
        'note',
        'lampiran',
        'jeniscuti',
        'approval1_id',
        'approval1_status',
        'approval2_id',
        'approval2_status',
        'approvalhr_status',
        'approval_status'
    ];
}
