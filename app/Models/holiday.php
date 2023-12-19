<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class holiday extends Model
{
    use HasFactory;
    protected $connection = 'mysql2';
    protected $table = 'holiday';

    protected $fillable = [
        'date',
        'note',
    ];
}
