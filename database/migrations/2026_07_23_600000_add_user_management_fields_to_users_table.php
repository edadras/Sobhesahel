<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Roadmap section 11 (مدیریت کاربران) remainder:
 *  - is_active : enable/disable a panel user (enforced in User::canAccessPanel()).
 *  - locale / timezone : per-user panel language and timezone preferences
 *    (applied by App\Http\Middleware\SetUserPreferences).
 *  - latitude / longitude : optional map location of the user (OpenStreetMap
 *    preview in UserResource).
 *
 * All columns are guarded with hasColumn so the migration is safe to re-run
 * on environments where some columns may already exist.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('email');
            }

            if (! Schema::hasColumn('users', 'locale')) {
                $table->string('locale', 10)->nullable()->after('is_active');
            }

            if (! Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone', 64)->nullable()->after('locale');
            }

            if (! Schema::hasColumn('users', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('timezone');
            }

            if (! Schema::hasColumn('users', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            foreach (['is_active', 'locale', 'timezone', 'latitude', 'longitude'] as $column) {
                if (Schema::hasColumn('users', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
