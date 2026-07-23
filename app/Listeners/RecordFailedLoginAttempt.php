<?php

namespace App\Listeners;

use App\Services\LoginSecurityService;
use Illuminate\Auth\Events\Failed;

/**
 * Feeds every failed authentication attempt (any guard) into the cache-based
 * LoginSecurityService counters. Auto-discovered by Laravel 11 via the
 * type-hinted handle() method, exactly like SendFailedLoginSms.
 */
class RecordFailedLoginAttempt
{
    public function __construct(protected LoginSecurityService $security)
    {
    }

    public function handle(Failed $event): void
    {
        $username = (string) (
            $event->credentials['email']
            ?? $event->credentials['username']
            ?? $event->credentials['mobile']
            ?? ''
        );

        $this->security->recordFailure($username, request()->ip());
    }
}
