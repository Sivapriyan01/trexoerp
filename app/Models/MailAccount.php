<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailAccount extends Model
{
    protected $fillable = [
        'account_name',
        'email',
        'smtp_host',
        'smtp_port',
        'smtp_user',
        'smtp_password',
        'smtp_encryption',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'from_name',
        'is_default'
    ];
}
