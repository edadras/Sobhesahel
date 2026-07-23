<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * اعضای سایت (باشگاه اعضای صبح ساحل) — فاز ۱ ناحیه کاربران.
 *
 * Deliberately SEPARATE from the `users` table (تصمیم ۵-۵ / تداخل ت۴):
 * `users` belongs to the editorial/admin staff and grants Filament panel
 * access, while `members` is the public membership table used by the
 * `member` guard only. A member can therefore never reach /admin.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // Iranian mobile in normalized 09xxxxxxxxx form — the primary
            // login identifier (OTP flow).
            $table->string('mobile', 20)->unique();

            // Email + password are optional: they are only set later from the
            // member settings page and enable the email/password login tab.
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();

            $table->string('first_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();

            // Path on the "public" disk (members/avatars/...).
            $table->string('avatar')->nullable();

            // Hormozgan city (free string — options come from config/member.php).
            $table->string('city', 100)->nullable();
            $table->date('birth_date')->nullable();
            $table->text('bio')->nullable();

            // Preferred site language (Language::active() codes).
            $table->string('locale', 10)->default('fa');

            $table->boolean('is_active')->default(true)->index();

            $table->dateTime('mobile_verified_at')->nullable();
            $table->dateTime('last_login_at')->nullable();

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
