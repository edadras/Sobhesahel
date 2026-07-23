<x-filament-panels::page>
    @unless(\Illuminate\Support\Facades\Schema::hasTable('editor_ratings'))
        <x-filament::section>
            <p class="text-sm text-danger-600 dark:text-danger-400">
                جدول امتیازدهی دبیران هنوز ایجاد نشده است؛ لطفاً مایگریشن‌ها را اجرا کنید.
            </p>
        </x-filament::section>
    @endunless

    {{ $this->table }}
</x-filament-panels::page>
