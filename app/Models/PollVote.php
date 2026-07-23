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

    /**
     * ثبت رأی با کنترل ضدتقلب:
     * وقتی ورود اجباری است هر کاربر فقط یک رأی دارد،
     * در غیر این صورت هر IP (و در صورت ورود، هر کاربر) فقط یک رأی دارد.
     */
    public static function castVote(
        int $poll_id,
        int $option_id,
        ?int $user_id,
        ?string $user_ip = null,
        ?string $user_agent = null,
        bool $login_required = false
    ): bool {
        if (self::hasAlreadyVoted($poll_id, $user_id, $user_ip, $login_required)) {
            return false; // Already voted
        }

        self::create([
            'poll_id' => $poll_id,
            'poll_option_id' => $option_id,
            'user_id' => $user_id,
            'user_ip' => $user_ip,
            'user_agent' => $user_agent,
        ]);

        return true;
    }

    public static function hasAlreadyVoted(int $poll_id, ?int $user_id, ?string $user_ip, bool $login_required = false): bool
    {
        $query = self::where('poll_id', $poll_id);

        if ($login_required) {
            // One vote per logged-in user
            $query->where('user_id', $user_id);
        } else {
            // One vote per IP (and per user when logged in)
            $query->where(function ($q) use ($user_ip, $user_id) {
                $q->where('user_ip', $user_ip);

                if ($user_id) {
                    $q->orWhere('user_id', $user_id);
                }
            });
        }

        return $query->exists();
    }
}
