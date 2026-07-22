<?php

namespace App\Listeners;

use App\Helpers\ModirSmsHelper;
use Illuminate\Auth\Events\Failed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendFailedLoginSms
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        if (!$event->user) {
            return; // Ignore if user is not found
        }

        $user = $event->user;
        $phoneNumber = $user->mobile ?? null; // Assuming the user model has a phone field
        if (!$phoneNumber) {
            return; // Ignore if the user has no phone number
        }

        $ipAddress = request()->ip();
        $time = now()->format('H:i'); // Get the current time
        $browser = request()->header('User-Agent'); // Get the browser info
        $os = php_uname('s'); // Get OS (or use a package for better detection)

        // Send SMS using ModirSmsHelper
        ModirSmsHelper::send_failed_login($phoneNumber, $ipAddress, $time, $browser, $os);
    }
}
