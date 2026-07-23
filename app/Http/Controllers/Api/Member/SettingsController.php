<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use App\Http\Resources\Api\Member\MemberResource;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Morilog\Jalali\Jalalian;

/**
 * تنظیمات حساب عضو از اپ — پروفایل، آواتار، رمز عبور و زبان.
 * منطق موازی وب MemberSettingsController، سازگار با ورودی JSON/چندبخشی اپ.
 */
class SettingsController extends Controller
{
    use MemberApiResponses;

    /**
     * PUT /settings/profile
     */
    public function updateProfile(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        try {
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'max:100'],
                'last_name' => ['required', 'string', 'max:100'],
                'email' => ['nullable', 'email', 'max:255', 'unique:members,email,' . $member->id],
                'city' => ['nullable', 'string', 'max:100'],
                'birth_date' => ['nullable', 'string', 'max:20'],
                'bio' => ['nullable', 'string', 'max:1000'],
            ], [
                'first_name.required' => 'لطفاً نام خود را وارد کنید.',
                'last_name.required' => 'لطفاً نام خانوادگی خود را وارد کنید.',
                'email.email' => 'قالب ایمیل واردشده صحیح نیست.',
                'email.unique' => 'این ایمیل قبلاً برای عضو دیگری ثبت شده است.',
                'bio.max' => 'متن «درباره من» نباید بیشتر از ۱۰۰۰ حرف باشد.',
            ]);
        } catch (ValidationException $e) {
            return $this->fail('اطلاعات واردشده معتبر نیست.', 422, $e->errors());
        }

        $birthDate = null;

        if (filled($validated['birth_date'] ?? null)) {
            $birthDate = $this->parseBirthDate($validated['birth_date']);

            if ($birthDate === null) {
                return $this->fail('تاریخ تولد را به شکل ۱۳۷۰/۰۳/۱۵ (شمسی) یا 1991-06-05 وارد کنید.', 422, [
                    'birth_date' => ['تاریخ تولد معتبر نیست.'],
                ]);
            }
        }

        $member->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => filled($validated['email'] ?? null) ? mb_strtolower(trim($validated['email'])) : null,
            'city' => $validated['city'] ?? null,
            'birth_date' => $birthDate,
            'bio' => $validated['bio'] ?? null,
        ])->save();

        return $this->data((new MemberResource($member->refresh()))->toArray($request));
    }

    /**
     * POST /settings/avatar — multipart avatar → {avatar_url}
     */
    public function updateAvatar(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        $maxKb = max(1, (int) config('member.avatar.max_kb', 2048));
        $mimes = implode(',', (array) config('member.avatar.mimes', ['jpg', 'jpeg', 'png', 'webp']));

        try {
            $request->validate([
                'avatar' => ['required', 'file', 'image', 'mimes:' . $mimes, 'max:' . $maxKb],
            ], [
                'avatar.required' => 'لطفاً یک تصویر انتخاب کنید.',
                'avatar.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
                'avatar.max' => 'حجم تصویر بیش از حد مجاز است.',
            ]);
        } catch (ValidationException $e) {
            return $this->fail('تصویر انتخاب‌شده معتبر نیست.', 422, $e->errors());
        }

        try {
            $directory = (string) config('member.avatar.directory', 'members/avatars');
            $newPath = $request->file('avatar')->store($directory, 'public');

            if (! $newPath) {
                return $this->fail('بارگذاری تصویر با خطا مواجه شد.', 422);
            }

            $this->deleteAvatar($member->avatar);

            $member->forceFill(['avatar' => $newPath])->save();
        } catch (\Throwable) {
            return $this->fail('بارگذاری تصویر با خطا مواجه شد.', 422);
        }

        return $this->data(['avatar_url' => $member->avatarUrl()]);
    }

    /**
     * PUT /settings/password
     */
    public function updatePassword(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        $rules = ['password' => ['required', 'string', 'min:8', 'confirmed']];

        if ($member->hasPassword()) {
            $rules['current_password'] = ['required', 'string'];
        }

        try {
            $validated = $request->validate($rules, [
                'current_password.required' => 'لطفاً رمز عبور فعلی را وارد کنید.',
                'password.required' => 'لطفاً رمز عبور جدید را وارد کنید.',
                'password.min' => 'رمز عبور باید حداقل ۸ حرف باشد.',
                'password.confirmed' => 'تکرار رمز عبور با رمز جدید یکسان نیست.',
            ]);
        } catch (ValidationException $e) {
            return $this->fail('اطلاعات واردشده معتبر نیست.', 422, $e->errors());
        }

        if ($member->hasPassword() && ! Hash::check($validated['current_password'], $member->password)) {
            return $this->fail('رمز عبور فعلی صحیح نیست.', 422, [
                'current_password' => ['رمز عبور فعلی صحیح نیست.'],
            ]);
        }

        if (blank($member->email)) {
            return $this->fail('برای ورود با رمز عبور، ابتدا ایمیل خود را در بخش پروفایل ثبت کنید.', 422, [
                'password' => ['ابتدا ایمیل خود را ثبت کنید.'],
            ]);
        }

        $member->forceFill(['password' => $validated['password']])->save();

        return $this->data(['ok' => true]);
    }

    /**
     * PUT /settings/locale
     */
    public function updateLocale(Request $request)
    {
        /** @var Member $member */
        $member = $request->user();

        try {
            $validated = $request->validate([
                'locale' => ['required', 'string', 'in:fa,en'],
            ], [
                'locale.required' => 'لطفاً زبان را انتخاب کنید.',
                'locale.in' => 'زبان انتخاب‌شده معتبر نیست.',
            ]);
        } catch (ValidationException $e) {
            return $this->fail('زبان انتخاب‌شده معتبر نیست.', 422, $e->errors());
        }

        $member->forceFill(['locale' => $validated['locale']])->save();

        return $this->data((new MemberResource($member->refresh()))->toArray($request));
    }

    /**
     * پذیرش تاریخ تولد به دو شکل: میلادی (Y-m-d) یا شمسی (Y/m/d با ارقام فارسی).
     */
    protected function parseBirthDate(string $raw): ?Carbon
    {
        $raw = trim(Member::normalizeDigits($raw));

        // میلادی ISO
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
            try {
                $date = Carbon::createFromFormat('Y-m-d', $raw)->startOfDay();

                return ($date->isFuture() || $date->year < 1900) ? null : $date;
            } catch (\Throwable) {
                return null;
            }
        }

        // شمسی
        $jalali = str_replace('-', '/', $raw);

        try {
            $date = Jalalian::fromFormat('Y/m/d', $jalali)->toCarbon()->startOfDay();

            return ($date->isFuture() || $date->year < 1900) ? null : $date;
        } catch (\Throwable) {
            return null;
        }
    }

    protected function deleteAvatar(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable) {
            // A missing file must never break the request.
        }
    }
}
