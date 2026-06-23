<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LogIntegracion extends Model
{
    use HasFactory;

    protected $table = 'logs_integracion';

    protected $fillable = [
        'source_system',
        'metodo',
        'endpoint',
        'request_id',
        'idempotency_key',
        'http_status',
        'status',
        'request_payload',
        'response_payload',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'http_status' => 'integer',
        'request_payload' => 'array',
        'response_payload' => 'array',
        'processed_at' => 'datetime',
    ];
}
