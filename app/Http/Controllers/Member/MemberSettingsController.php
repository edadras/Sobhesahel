<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

/**
 * تنظیمات حساب عضو — پروفایل، رمز عبور و زبان.
 */
class MemberSettingsController extends Controller
{
    public function index()
    {
        /** @var Member $member */
        $member = Auth::guard('member')->user();

        return view('member.settings', [
            'member' => $member,
            'active' => 'settings',
            'cities' => (array) config('member.cities', []),
            'languages' => Language::active(),
            'birthDateJalali' => $member->birth_date
                ? Jalalian::fromCarbon($member->birth_date)->format('Y/m/d')
                : '',
        ]);
    }

    /**
     * ویرایش پروفایل (POST member/settings/profile).
     */
    public function updateProfile(Request $request)
    {
        /** @var Member $member */
        $member = Auth::guard('member')->user();

        $cities = (array) config('member.cities', []);
        $localeCodes = Language::active()->pluck('code')->all();
        $avatarMaxKb = max(1, (int) config('member.avatar.max_kb', 2048));
        $avatarMimes = implode(',', (array) config('member.avatar.mimes', ['jpg', 'jpeg', 'png', 'webp']));

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255', 'unique:members,email,' . $member->id],
            'city' => ['nullable', 'string', 'in:' . implode(',', $cities)],
            'birth_date' => ['nullable', 'string', 'max:20'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'locale' => ['nullable', 'string', 'in:' . implode(',', $localeCodes)],
            'avatar' => ['nullable', 'file', 'image', 'mimes:' . $avatarMimes, 'max:' . $avatarMaxKb],
            'remove_avatar' => ['nullable', 'boolean'],
        ], [
            'first_name.required' => 'لطفاً نام خود را وارد کنید.',
            'first_name.max' => 'نام نباید بیشتر از ۱۰۰ حرف باشد.',
            'last_name.required' => 'لطفاً نام خانوادگی خود را وارد کنید.',
            'last_name.max' => 'نام خانوادگی نباید بیشتر از ۱۰۰ حرف باشد.',
            'email.email' => 'قالب ایمیل واردشده صحیح نیست.',
            'email.unique' => 'این ایمیل قبلاً برای عضو دیگری ثبت شده است.',
            'city.in' => 'شهر انتخاب‌شده معتبر نیست.',
            'bio.max' => 'متن «درباره من» نباید بیشتر از ۱۰۰۰ حرف باشد.',
            'locale.in' => 'زبان انتخاب‌شده معتبر نیست.',
            'avatar.image' => 'فایل انتخاب‌شده باید تصویر باشد.',
            'avatar.mimes' => 'قالب تصویر باید ' . str_replace(',', '، ', $avatarMimes) . ' باشد.',
            'avatar.max' => 'حجم تصویر نباید بیشتر از ' . round($avatarMaxKb / 1024, 1) . ' مگابایت باشد.',
        ]);

        // تاریخ تولد — Jalali-friendly input like ۱۳۷۰/۰۳/۱۵ (Persian or
        // ASCII digits, with / or - separators).
        $birthDate = null;

        if (filled($validated['birth_date'] ?? null)) {
            $raw = str_replace('-', '/', Member::normalizeDigits(trim($validated['birth_date'])));

            try {
                $birthDate = Jalalian::fromFormat('Y/m/d', $raw)->toCarbon()->startOfDay();
            } catch (\Throwable) {
                return back()
                    ->withInput()
                    ->withErrors(['birth_date' => 'تاریخ تولد را به شکل ۱۳۷۰/۰۳/۱۵ (شمسی) وارد کنید.']);
            }

            if ($birthDate->isFuture() || $birthDate->year < 1900) {
                return back()
                    ->withInput()
                    ->withErrors(['birth_date' => 'تاریخ تولد واردشده معتبر نیست.']);
            }
        }

        // آواتار — upload / remove on the public disk.
        $avatarPath = $member->avatar;
        $directory = (string) config('member.avatar.directory', 'members/avatars');

        if ($request->boolean('remove_avatar')) {
            $this->deleteAvatar($avatarPath);
            $avatarPath = null;
        }

        if ($request->hasFile('avatar')) {
            $newPath = $request->file('avatar')->store($directory, 'public');

            if ($newPath) {
                $this->deleteAvatar($member->avatar);
                $avatarPath = $newPath;
            }
        }

        $member->forceFill([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => filled($validated['email'] ?? null) ? mb_strtolower(trim($validated['email'])) : null,
            'city' => $validated['city'] ?? null,
            'birth_date' => $birthDate,
            'bio' => $validated['bio'] ?? null,
            'locale' => $validated['locale'] ?? $member->locale ?? 'fa',
            'avatar' => $avatarPath,
        ])->save();

        return redirect()->route('member.settings')
            ->with('status', 'تغییرات پروفایل با موفقیت ذخیره شد.');
    }

    /**
     * تعیین / تغییر رمز عبور (POST member/settings/password).
     * The current password is only required when one already exists.
     */
    public function updatePassword(Request $request)
    {
        /** @var Member $member */
        $member = Auth::guard('member')->user();

        $rules = [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        if ($member->hasPassword()) {
            $rules['current_password'] = ['required', 'string'];
        }

        $validated = $request->validate($rules, [
            'current_password.required' => 'لطفاً رمز عبور فعلی را وارد کنید.',
            'password.required' => 'لطفاً رمز عبور جدید را وارد کنید.',
            'password.min' => 'رمز عبور باید حداقل ۸ حرف باشد.',
            'password.confirmed' => 'تکرار رمز عبور با رمز جدید یکسان نیست.',
        ]);

        if ($member->hasPassword() && ! Hash::check($validated['current_password'], $member->password)) {
            return back()->withErrors(['current_password' => 'رمز عبور فعلی صحیح نیست.']);
        }

        if (blank($member->email)) {
            return back()->withErrors(['password' => 'برای ورود با رمز عبور، ابتدا ایمیل خود را در بخش پروفایل ثبت کنید.']);
        }

        // The `hashed` cast bcrypts the value on assignment.
        $member->forceFill(['password' => $validated['password']])->save();

        return redirect()->route('member.settings')
            ->with('status', 'رمز عبور با موفقیت به‌روزرسانی شد. از این پس می‌توانید با ایمیل و رمز نیز وارد شوید.');
    }

    /**
     * تغییر زبان سایت (POST member/settings/locale) — lightweight so it works
     * even before the profile is completed.
     */
    public function updateLocale(Request $request)
    {
        /** @var Member $member */
        $member = Auth::guard('member')->user();

        $localeCodes = Language::active()->pluck('code')->all();

        $validated = $request->validate([
            'locale' => ['required', 'string', 'in:' . implode(',', $localeCodes)],
        ], [
            'locale.required' => 'لطفاً زبان را انتخاب کنید.',
            'locale.in' => 'زبان انتخاب‌شده معتبر نیست.',
        ]);

        $member->forceFill(['locale' => $validated['locale']])->save();

        return redirect()->route('member.settings')
            ->with('status', 'زبان حساب با موفقیت ذخیره شد.');
    }

    protected function deleteAvatar(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        try {
            Storage::disk('public')->delete($path);
        } catch (\Throwable) {
            // A missing file must never break the settings form.
        }
    }
}
