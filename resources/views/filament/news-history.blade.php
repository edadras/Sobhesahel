<div class="space-y-4" dir="rtl">
    @forelse ($revisions as $revision)
        <div class="rounded-lg border border-gray-200 p-4 dark:border-white/10">
            <div class="flex items-center justify-between gap-3">
                <span @class([
                    'fi-badge inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                    'bg-success-50 text-success-700 dark:bg-success-400/10 dark:text-success-400' => $revision->action === 'created',
                    'bg-info-50 text-info-700 dark:bg-info-400/10 dark:text-info-400' => $revision->action === 'updated',
                    'bg-warning-50 text-warning-700 dark:bg-warning-400/10 dark:text-warning-400' => $revision->action === 'status_changed',
                    'bg-danger-50 text-danger-700 dark:bg-danger-400/10 dark:text-danger-400' => $revision->action === 'deleted',
                    'bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-400' => $revision->action === 'restored',
                ])>
                    {{ $revision->action_label }}
                </span>

                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ $revision->created_at
                        ? \Morilog\Jalali\Jalalian::fromCarbon(\Carbon\Carbon::parse($revision->created_at))->format('H:i Y/m/d')
                        : '—' }}
                </span>
            </div>

            <div class="mt-2 text-sm text-gray-700 dark:text-gray-300">
                کاربر:
                <span class="font-medium">
                    {{ $revision->user?->name ?? 'نامشخص' }}
                </span>
            </div>

            @if (!empty($revision->changed_fields_summary))
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-right text-gray-500 dark:text-gray-400">
                                <th class="py-1 pe-4 font-medium">فیلد</th>
                                <th class="py-1 pe-4 font-medium">مقدار قبلی</th>
                                <th class="py-1 font-medium">مقدار جدید</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($revision->changed_fields_summary as $change)
                                <tr class="border-t border-gray-100 dark:border-white/5">
                                    <td class="py-1 pe-4 font-medium text-gray-700 dark:text-gray-300">
                                        {{ $change['label'] }}
                                    </td>
                                    <td class="py-1 pe-4 text-gray-500 dark:text-gray-400">
                                        {{ $change['old'] }}
                                    </td>
                                    <td class="py-1 text-gray-700 dark:text-gray-200">
                                        {{ $change['new'] }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @empty
        <p class="text-sm text-gray-500 dark:text-gray-400">
            تاریخچه‌ای برای این خبر ثبت نشده است.
        </p>
    @endforelse
</div>
