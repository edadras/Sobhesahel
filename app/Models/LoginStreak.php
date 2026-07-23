<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * زنجیره ورود روزانه عضو — تاریخ‌ها به‌وقت Asia/Tehran محاسبه می‌شوند.
 */
class LoginStreak extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'member_id' => 'integer',
        'current_days' => 'integer',
        'longest_days' => 'integer',
        'last_login_date' => 'date',
    ];
}
