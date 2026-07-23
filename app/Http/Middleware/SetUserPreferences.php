<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Applies the authenticated user's personal panel preferences (users.locale
 * and users.timezone) to the current request:
 *
 *  - locale   -> app()->setLocale() (+ Carbon translation locale), so panel
 *               strings/dates follow the user's "زبان پنل" choice.
 *  - timezone -> config('app.user_timezone') is set for display-layer
 *               consumers, and date_default_timezone_set() adjusts rendered
 *               times. Database timestamps keep being written by the DB/app
 *               defaults, so stored data stays consistent.
 *
 * Enablement (one line, owned by AdminPanelProvider): add
 *     \App\Http\Middleware\SetUserPreferences::class,
 * to the panel ->middleware([...]) stack (after AuthenticateSession) — or
 * register it globally in bootstrap/app.php if the whole site should honour
 * these preferences.
 *
 * Defensive by design: unknown locales/timezones are ignored, and any failure
 * falls through silently so the panel can never break because of a bad
 * preference value.
 */
class SetUserPreferences
{
    /** Locales the panel actually ships translations for. */
    protected const SUPPORTED_LOCALES = ['fa', 'en'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            $this->applyLocale($user->locale ?? null);
            $this->applyTimezone($user->timezone ?? null);
        }

        return $next($request);
    }

    protected function applyLocale(?string $locale): void
    {
        if (! is_string($locale) || ! in_array($locale, self::SUPPORTED_LOCALES, true)) {
            return;
        }

        rescue(function () use ($locale) {
            app()->setLocale($locale);
            \Illuminate\Support\Carbon::setLocale($locale);
        }, report: false);
    }

    protected function applyTimezone(?string $timezone): void
    {
        if (! is_string($timezone) || $timezone === '') {
            return;
        }

        if (! in_array($timezone, timezone_identifiers_list(), true)) {
            return;
        }

        rescue(function () use ($timezone) {
            config(['app.user_timezone' => $timezone]);
            date_default_timezone_set($timezone);
        }, report: false);
    }
}
