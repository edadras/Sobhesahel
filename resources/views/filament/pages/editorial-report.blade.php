<x-filament-panels::page>
    @php
        use Morilog\Jalali\CalendarUtils;
        use Morilog\Jalali\Jalalian;

        $report = $this->getReport();
        $typeLabels = \App\Filament\Pages\EditorialReport::TYPE_LABELS;

        $fa = fn ($number) => CalendarUtils::convertNumbers(number_format((float) $number));
        $faDecimal = fn ($number) => CalendarUtils::convertNumbers(number_format((float) $number, 2));
    @endphp

    <x-filament::section>
        <x-slot name="heading">فیلتر گزارش</x-slot>

        <form wire:submit="applyFilters" class="space-y-4">
            {{ $this->form }}

            <x-filament::button type="submit" icon="heroicon-o-funnel">
                اعمال فیلتر
            </x-filament::button>
        </form>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">گزارش فعالیت به تفکیک کاربر</x-slot>
        <x-slot name="description">
            بازه گزارش:
            {{ CalendarUtils::convertNumbers(Jalalian::fromCarbon($report['from'])->format('Y/m/d')) }}
            تا
            {{ CalendarUtils::convertNumbers(Jalalian::fromCarbon($report['to'])->format('Y/m/d')) }}
            — مطالب منتشرشده بر اساس تاریخ انتشار
        </x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-start divide-y divide-gray-200 dark:divide-white/10">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400">
                        <th class="px-3 py-2 text-start font-semibold">کاربر</th>
                        @foreach($typeLabels as $label)
                            <th class="px-3 py-2 text-center font-semibold">{{ $label }}</th>
                        @endforeach
                        <th class="px-3 py-2 text-center font-semibold">مجموع مطالب</th>
                        <th class="px-3 py-2 text-center font-semibold">مجموع بازدید</th>
                        <th class="px-3 py-2 text-center font-semibold">تعداد ویرایش‌ها</th>
                        <th class="px-3 py-2 text-center font-semibold">میانگین امتیاز دبیران</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($report['rows'] as $row)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">
                                {{ $row['name'] }}
                            </td>
                            @foreach(array_keys($typeLabels) as $type)
                                <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-200">
                                    {{ $fa($row['counts'][$type]) }}
                                </td>
                            @endforeach
                            <td class="px-3 py-2 text-center font-semibold text-gray-950 dark:text-white">
                                {{ $fa($row['total']) }}
                            </td>
                            <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-200">
                                {{ $fa($row['visits']) }}
                            </td>
                            <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-200">
                                {{ $fa($row['edits']) }}
                            </td>
                            <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-200">
                                @if($row['editor_score'] !== null)
                                    {{ $faDecimal($row['editor_score']) }}
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ $fa($row['editor_score_count']) }} امتیاز)
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ count($typeLabels) + 5 }}" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                در بازه انتخاب‌شده مطلبی یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($report['rows']))
                    <tfoot>
                        <tr class="bg-gray-50 dark:bg-white/5 font-bold text-gray-950 dark:text-white">
                            <td class="px-3 py-2">جمع کل</td>
                            @foreach(array_keys($typeLabels) as $type)
                                <td class="px-3 py-2 text-center">{{ $fa($report['totals']['counts'][$type]) }}</td>
                            @endforeach
                            <td class="px-3 py-2 text-center">{{ $fa($report['totals']['total']) }}</td>
                            <td class="px-3 py-2 text-center">{{ $fa($report['totals']['visits']) }}</td>
                            <td class="px-3 py-2 text-center">{{ $fa($report['totals']['edits']) }}</td>
                            <td class="px-3 py-2 text-center">—</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </x-filament::section>

    <x-filament::section>
        <x-slot name="heading">تفکیک اخبار بر اساس سرویس</x-slot>
        <x-slot name="description">تعداد اخبار منتشرشده هر کاربر در هر سرویس (فقط اخبار دارای سرویس)</x-slot>

        <div class="overflow-x-auto">
            <table class="w-full text-sm divide-y divide-gray-200 dark:divide-white/10">
                <thead>
                    <tr class="text-gray-500 dark:text-gray-400">
                        <th class="px-3 py-2 text-start font-semibold">کاربر</th>
                        <th class="px-3 py-2 text-start font-semibold">سرویس</th>
                        <th class="px-3 py-2 text-center font-semibold">تعداد خبر</th>
                        <th class="px-3 py-2 text-center font-semibold">مجموع بازدید</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($report['services'] as $row)
                        <tr>
                            <td class="px-3 py-2 font-medium text-gray-950 dark:text-white">{{ $row['name'] }}</td>
                            <td class="px-3 py-2 text-gray-700 dark:text-gray-200">{{ $row['category'] }}</td>
                            <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-200">{{ $fa($row['total']) }}</td>
                            <td class="px-3 py-2 text-center text-gray-700 dark:text-gray-200">{{ $fa($row['visits']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-6 text-center text-gray-500 dark:text-gray-400">
                                در بازه انتخاب‌شده خبری با سرویس یافت نشد.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>
</x-filament-panels::page>
