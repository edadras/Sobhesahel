<x-filament-widgets::widget>
    <x-filament::section heading="اعضای آنلاین تحریریه" description="کاربران فعال پنل در ۵ دقیقه گذشته">
        <div wire:poll.60s>
            @if ($onlineUsers->isEmpty())
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    در حال حاضر کاربری آنلاین نیست.
                </p>
            @else
                <ul class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($onlineUsers as $user)
                        <li class="flex items-center gap-3 py-2">
                            <span class="relative inline-block">
                                @if (! empty($user['avatar']))
                                    <img
                                        src="{{ $user['avatar'] }}"
                                        alt="{{ $user['name'] }}"
                                        class="h-8 w-8 rounded-full object-cover"
                                    />
                                @else
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-gray-100 text-xs font-medium text-gray-600 dark:bg-white/10 dark:text-gray-300">
                                        {{ mb_substr($user['name'], 0, 1) }}
                                    </span>
                                @endif
                                <span class="absolute -bottom-0.5 -start-0.5 h-2.5 w-2.5 rounded-full bg-success-500 ring-2 ring-white dark:ring-gray-900"></span>
                            </span>

                            <span class="flex-1 text-sm font-medium text-gray-950 dark:text-white">
                                {{ $user['name'] }}
                            </span>

                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                آخرین فعالیت: {{ $user['last_activity_time'] }}
                                @if ($user['last_activity_diff'] < 1)
                                    (لحظاتی پیش)
                                @else
                                    ({{ $user['last_activity_diff'] }} دقیقه پیش)
                                @endif
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
