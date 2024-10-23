<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class ChatLog extends Model
{
    protected $table = 'chat_logs';

    protected $fillable = [
        'user_message',
        'bot_response',
    ];

    // Logging for ChatLog creation
    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            Log::info('Creating ChatLog: ' . json_encode($model));
        });

        static::created(function ($model) {
            Log::info('ChatLog created successfully.');
        });
    }
}
