<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class OperationLog extends Model
{
    use HasFactory;

    protected $table = 'operation_logs';

    protected $fillable = [
        'user_id',
        'method',
        'path',
        'payload',
        'status',
    ];
}
