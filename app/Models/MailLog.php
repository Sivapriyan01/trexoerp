<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailLog extends Model
{
    protected $fillable = [
        'subject',
        'recipient',
        'message',
        'type',
        'recipients_count',
        'success_count',
        'failed_count',
        'sent_by'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'sent_by');
    }
}
