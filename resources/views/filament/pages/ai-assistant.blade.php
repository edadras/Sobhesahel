<x-filament-panels::page>
    @php
        $aiEnabled = $this->aiEnabled();
    @endphp

    @unless ($aiEnabled)
        <x-filament::section>
            <div class="flex items-start gap-3 text-sm text-warning-600 dark:text-warning-400">
                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 shrink-0" />
                <p>
                    سرویس هوش مصنوعی هنوز پیکربندی نشده است. برای فعال‌سازی، متغیرهای
                    <code dir="ltr">AI_ENABLED</code>،
                    <code dir="ltr">AI_BASE_URL</code>،
                    <code dir="ltr">AI_API_KEY</code>
                    و
                    <code dir="ltr">AI_MODEL</code>
                    را در تنظیمات محیط سایت مقداردهی کنید. (هر سرویس سازگار با OpenAI قابل استفاده است.)
                </p>
            </div>
        </x-filament::section>
    @endunless

    <form wire:submit.prevent class="space-y-6">
        {{ $this->form }}

        <div class="flex flex-wrap items-center gap-3">
            <x-filament::button
                wire:click="generateAd"
                icon="heroicon-m-sparkles"
                :disabled="! $aiEnabled"
                wire:loading.attr="disabled"
            >
                تولید متن تبلیغاتی
            </x-filament::button>

            <x-filament::button
                color="info"
                wire:click="generateHeadlines"
                icon="heroicon-m-light-bulb"
                :disabled="! $aiEnabled"
                wire:loading.attr="disabled"
            >
                پیشنهاد تیتر برای متن
            </x-filament::button>

            <x-filament::button
                color="gray"
                wire:click="generateSummary"
                icon="heroicon-m-document-text"
                :disabled="! $aiEnabled"
                wire:loading.attr="disabled"
            >
                خلاصه‌سازی متن
            </x-filament::button>

            <span wire:loading class="text-sm text-gray-500 dark:text-gray-400">
                در حال دریافت پاسخ از هوش مصنوعی…
            </span>
        </div>
    </form>

    @if ($adResult)
        <x-filament::section>
            <x-slot name="heading">متن تبلیغاتی تولیدشده</x-slot>
            <p class="whitespace-pre-line text-sm leading-7 text-gray-700 dark:text-gray-200" dir="rtl">
                {{ $adResult }}
            </p>
        </x-filament::section>
    @endif

    @if (! empty($headlineResults))
        <x-filament::section>
            <x-slot name="heading">تیترهای پیشنهادی</x-slot>
            <ol class="list-decimal space-y-2 pr-5 text-sm text-gray-700 dark:text-gray-200" dir="rtl">
                @foreach ($headlineResults as $headline)
                    <li>{{ $headline }}</li>
                @endforeach
            </ol>
        </x-filament::section>
    @endif

    @if ($summaryResult)
        <x-filament::section>
            <x-slot name="heading">خلاصه متن</x-slot>
            <p class="whitespace-pre-line text-sm leading-7 text-gray-700 dark:text-gray-200" dir="rtl">
                {{ $summaryResult }}
            </p>
        </x-filament::section>
    @endif
</x-filament-panels::page>
