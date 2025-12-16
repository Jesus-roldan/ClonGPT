<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatInstruction extends Model
{
    protected $fillable = [
        'user_id',
        'about_you',
        'behaviour',
        'commands',
    ];

    protected $casts = [
        'about' => 'array',
        'behaviour' => 'array',
        'commands' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
