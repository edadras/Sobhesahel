<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PollVote extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function poll()
    {
        return $this->belongsTo(Poll::class);
    }

    public function option()
    {
        return $this->belongsTo(PollOption::class);
    }

    public static function castVote(int $poll_id, int $option_id, int $user_id): bool
    {
        if (self::where(['poll_id' => $poll_id, 'user_id' => $user_id])->exists()) {
            return false; // User already voted
        }

        self::create([
            'poll_id' => $poll_id,
            'poll_option_id' => $option_id,
            'user_id' => $user_id
        ]);

        return true;
    }
}
