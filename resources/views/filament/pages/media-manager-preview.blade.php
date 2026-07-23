<div class="space-y-3">
    <div class="flex items-center justify-center rounded-lg bg-gray-50 p-2 dark:bg-white/5">
        @if (($file['type'] ?? 'other') === 'image' && ! empty($file['url']))
            <img
                src="{{ $file['url'] }}"
                alt="{{ $file['name'] ?? '' }}"
                class="max-h-96 w-auto max-w-full rounded"
            />
        @elseif (($file['type'] ?? 'other') === 'audio' && ! empty($file['url']))
            <audio controls preload="metadata" class="w-full" dir="ltr">
                <source src="{{ $file['url'] }}" />
                مرورگر شما از پخش صوت پشتیبانی نمی‌کند.
            </audio>
        @elseif (($file['type'] ?? 'other') === 'video' && ! empty($file['url']))
            <video controls preload="metadata" class="max-h-96 w-full rounded" dir="ltr">
                <source src="{{ $file['url'] }}" />
                مرورگر شما از پخش ویدئو پشتیبانی نمی‌کند.
            </video>
        @else
            <p class="p-6 text-sm text-gray-500 dark:text-gray-400">
                پیش‌نمایش برای این نوع فایل در دسترس نیست.
            </p>
        @endif
    </div>

    <div class="flex flex-wrap items-center justify-between gap-2 text-xs text-gray-500 dark:text-gray-400">
        <span>
            {{ $file['size_human'] ?? '' }}
            @if (! empty($file['modified_jalali']))
                <span class="mx-1">·</span>
                <span dir="ltr">{{ $file['modified_jalali'] }}</span>
            @endif
        </span>
    </div>

    @if (! empty($file['url']))
        <p class="break-all rounded bg-gray-100 p-2 font-mono text-xs text-gray-600 dark:bg-white/10 dark:text-gray-300" dir="ltr">
            {{ $file['url'] }}
        </p>
    @endif
</div>
