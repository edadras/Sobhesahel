<?php

namespace App\Filament\Pages\Auth;

use AbanoubNassem\FilamentGRecaptchaField\Forms\Components\GRecaptcha;
use App\Services\LoginSecurityService;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Models\Contracts\FilamentUser;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;

class Login extends \Filament\Pages\Auth\Login
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                // --- classic username / password login -------------------
                $this->getEmailFormComponent()
                    ->visible(fn (Get $get): bool => ! $get('use_otp')),
                $this->getPasswordFormComponent()
                    ->visible(fn (Get $get): bool => ! $get('use_otp')),
                $this->getRememberFormComponent()
                    ->visible(fn (Get $get): bool => ! $get('use_otp')),

                // Captcha appears only after N failed attempts and only when
                // a reCAPTCHA site key is configured (login never breaks).
                ...(static::captchaAvailable() ? [
                    GRecaptcha::make('captcha')
                        ->visible(fn (Get $get): bool => ! $get('use_otp') && $this->captchaRequired()),
                ] : []),

                // --- one-time password (SMS) login -----------------------
                Checkbox::make('use_otp')
                    ->label('ورود با رمز یکبارمصرف (پیامکی)')
                    ->live()
                    ->dehydrated(false),
                TextInput::make('mobile')
                    ->label('شماره موبایل')
                    ->tel()
                    ->visible(fn (Get $get): bool => (bool) $get('use_otp')),
                Actions::make([
                    Action::make('sendOtp')
                        ->label('ارسال کد')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->action(fn (Get $get) => $this->sendOtp((string) $get('mobile'))),
                ])->visible(fn (Get $get): bool => (bool) $get('use_otp')),
                TextInput::make('otp_code')
                    ->label('کد پیامک‌شده')
                    ->numeric()
                    ->visible(fn (Get $get): bool => (bool) $get('use_otp')),
            ])
            ->statePath('data');
    }

    public function authenticate(): ?LoginResponse
    {
        $security = app(LoginSecurityService::class);
        $ip = request()->ip();

        if ($security->isIpBlocked($ip)) {
            throw ValidationException::withMessages([
                'data.email' => 'دسترسی شما به دلیل تلاش‌های ناموفق مکرر، موقتاً مسدود شده است. لطفاً بعداً دوباره تلاش کنید.',
            ]);
        }

        if ((bool) ($this->data['use_otp'] ?? false)) {
            return $this->authenticateWithOtp($security, (string) $ip);
        }

        $username = (string) ($this->data['email'] ?? '');

        if (($minutes = $security->lockedRemainingMinutes($username)) > 0) {
            throw ValidationException::withMessages([
                'data.email' => "این حساب کاربری به دلیل تلاش‌های ناموفق مکرر قفل شده است. لطفاً {$minutes} دقیقه دیگر دوباره تلاش کنید.",
            ]);
        }

        // Failed attempts are counted by the Illuminate\Auth\Events\Failed
        // listener (App\Listeners\RecordFailedLoginAttempt); parent throws on
        // bad credentials, so reaching a non-null response means success.
        $response = parent::authenticate();

        if ($response !== null) {
            $security->clearFailures($username, (string) $ip);
        }

        return $response;
    }

    protected function authenticateWithOtp(LoginSecurityService $security, string $ip): ?LoginResponse
    {
        $mobile = $security->normalizeMobile((string) ($this->data['mobile'] ?? ''));
        $code = (string) ($this->data['otp_code'] ?? '');

        if ($mobile === '' || trim($code) === '') {
            throw ValidationException::withMessages([
                'data.otp_code' => 'لطفاً شماره موبایل و کد پیامک‌شده را وارد کنید.',
            ]);
        }

        if (($minutes = $security->lockedRemainingMinutes($mobile)) > 0) {
            throw ValidationException::withMessages([
                'data.otp_code' => "این حساب کاربری به دلیل تلاش‌های ناموفق مکرر قفل شده است. لطفاً {$minutes} دقیقه دیگر دوباره تلاش کنید.",
            ]);
        }

        $user = $security->verifyOtp($mobile, $code);

        if (! $user) {
            $security->recordFailure($mobile, $ip);

            throw ValidationException::withMessages([
                'data.otp_code' => 'کد واردشده معتبر نیست یا منقضی شده است.',
            ]);
        }

        Filament::auth()->login($user);

        if ($user instanceof FilamentUser && ! $user->canAccessPanel(Filament::getCurrentPanel())) {
            Filament::auth()->logout();

            $this->throwFailureValidationException();
        }

        session()->regenerate();

        $security->clearFailures($mobile, $ip);

        return app(LoginResponse::class);
    }

    public function sendOtp(string $mobile): void
    {
        $security = app(LoginSecurityService::class);

        if ($security->isIpBlocked(request()->ip())) {
            Notification::make()
                ->title('دسترسی شما موقتاً مسدود شده است.')
                ->danger()
                ->send();

            return;
        }

        [$ok, $message] = $security->requestOtp($mobile);

        Notification::make()
            ->title($message)
            ->{$ok ? 'success' : 'danger'}()
            ->send();
    }

    protected function captchaRequired(): bool
    {
        return app(LoginSecurityService::class)->captchaRequired(
            (string) ($this->data['email'] ?? ''),
            request()->ip()
        );
    }

    protected static function captchaAvailable(): bool
    {
        return class_exists(GRecaptcha::class)
            && filled(config('captcha.sitekey'))
            && filled(config('captcha.secret'));
    }
}
