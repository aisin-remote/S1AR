<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class jenisizin extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'jenisizin';

    protected $fillable = [
        'jenisizin',
    ];
}
