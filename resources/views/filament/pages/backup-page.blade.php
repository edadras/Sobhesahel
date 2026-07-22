<x-filament-panels::page>
    @php($backups = $this->getBackups())

    <x-filament::section>
        <x-slot name="heading">فایل‌های پشتیبان</x-slot>
        <x-slot name="description">
            نسخه‌های پشتیبان پایگاه داده و فایل‌ها در storage/app/backups نگهداری می‌شوند.
        </x-slot>

        @if (count($backups) === 0)
            <p class="text-sm text-gray-500 dark:text-gray-400">
                هنوز هیچ نسخه پشتیبانی ساخته نشده است. با دکمه «اجرای پشتیبان‌گیری» اولین نسخه را بسازید.
            </p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-right">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400">
                            <th class="py-2 px-3 font-medium">نوع</th>
                            <th class="py-2 px-3 font-medium">نام فایل</th>
                            <th class="py-2 px-3 font-medium">حجم</th>
                            <th class="py-2 px-3 font-medium">تاریخ ساخت</th>
                            <th class="py-2 px-3 font-medium">دانلود</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($backups as $backup)
                            <tr class="border-b border-gray-100 dark:border-white/5">
                                <td class="py-2 px-3">
                                    <x-filament::badge :color="$backup['type'] === 'db' ? 'info' : 'warning'">
                                        {{ $backup['type'] === 'db' ? 'پایگاه داده' : 'فایل‌ها' }}
                                    </x-filament::badge>
                                </td>
                                <td class="py-2 px-3 font-mono text-xs" dir="ltr">{{ $backup['name'] }}</td>
                                <td class="py-2 px-3 whitespace-nowrap" dir="ltr">{{ $backup['size_for_humans'] }}</td>
                                <td class="py-2 px-3 whitespace-nowrap" dir="ltr">{{ $backup['modified']->format('Y-m-d H:i') }}</td>
                                <td class="py-2 px-3">
                                    <x-filament::button
                                        size="xs"
                                        color="gray"
                                        icon="heroicon-o-arrow-down-tray"
                                        wire:click="download('{{ $backup['type'] }}', '{{ $backup['name'] }}')"
                                    >
                                        دانلود
                                    </x-filament::button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
