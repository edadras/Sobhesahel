<x-filament-widgets::widget>
    <div class="grid grid-cols-1 gap-6 {{ ($matomoConfigured ?? false) ? 'lg:grid-cols-2' : '' }}" wire:poll.120s>
        <x-filament::section heading="پربازدیدترین مطالب">
            @if ($items->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    هنوز آماری برای نمایش وجود ندارد.
                </p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 text-right dark:border-white/10">
                                <th class="py-2 pe-3 font-medium text-gray-500 dark:text-gray-400">#</th>
                                <th class="py-2 pe-3 font-medium text-gray-500 dark:text-gray-400">عنوان</th>
                                <th class="py-2 pe-3 font-medium text-gray-500 dark:text-gray-400">نوع</th>
                                <th class="py-2 pe-3 font-medium text-gray-500 dark:text-gray-400">تاریخ انتشار</th>
                                <th class="py-2 font-medium text-gray-500 dark:text-gray-400">بازدید</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                                <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                    <td class="py-2 pe-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                    <td class="py-2 pe-3 font-medium text-gray-950 dark:text-white">
                                        {{ \Illuminate\Support\Str::limit($item['title'], 60) }}
                                    </td>
                                    <td class="py-2 pe-3">
                                        <x-filament::badge color="info">
                                            {{ $item['type'] }}
                                        </x-filament::badge>
                                    </td>
                                    <td class="py-2 pe-3 text-gray-500 dark:text-gray-400">
                                        {{ $item['publish_at_jalali'] }}
                                    </td>
                                    <td class="py-2 font-semibold text-primary-600 dark:text-primary-400">
                                        {{ number_format($item['visits']) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        @if ($matomoConfigured ?? false)
            <x-filament::section heading="پربازدیدترین صفحات امروز (ماتومو)">
                @if (blank($matomoPages))
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        — ماتومو در دسترس نیست یا داده‌ای ثبت نشده است.
                    </p>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-200 text-right dark:border-white/10">
                                    <th class="py-2 pe-3 font-medium text-gray-500 dark:text-gray-400">#</th>
                                    <th class="py-2 pe-3 font-medium text-gray-500 dark:text-gray-400">صفحه</th>
                                    <th class="py-2 font-medium text-gray-500 dark:text-gray-400">بازدید</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($matomoPages as $index => $page)
                                    <tr class="border-b border-gray-100 last:border-0 dark:border-white/5">
                                        <td class="py-2 pe-3 text-gray-500 dark:text-gray-400">{{ $index + 1 }}</td>
                                        <td class="py-2 pe-3 font-medium text-gray-950 dark:text-white">
                                            {{ \Illuminate\Support\Str::limit($page['label'], 70) }}
                                        </td>
                                        <td class="py-2 font-semibold text-primary-600 dark:text-primary-400">
                                            {{ number_format($page['hits']) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-filament::section>
        @endif
    </div>
</x-filament-widgets::widget>
