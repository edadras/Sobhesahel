@php
    $poll_options = $poll->options()->withCount('votes')->get();
    $total_votes = $poll_options->sum('votes_count');
@endphp

<div dir="rtl" style="padding: 4px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; padding: 10px 12px; border-radius: 8px; background: rgba(59, 130, 246, 0.1);">
        <span style="font-weight: 600;">مجموع شرکت‌کنندگان</span>
        <span style="font-weight: 700;">{{ number_format($total_votes) }} نفر</span>
    </div>

    @forelse($poll_options as $option)
        @php
            $percent = $total_votes > 0 ? round(($option->votes_count / $total_votes) * 100, 1) : 0;
        @endphp
        <div style="margin-bottom: 14px;">
            <div style="display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 4px;">
                <span>{{ $option->option_text }}</span>
                <span>{{ number_format($option->votes_count) }} رأی ({{ $percent }}٪)</span>
            </div>
            <div style="background: rgba(0, 0, 0, 0.1); border-radius: 6px; height: 12px; overflow: hidden;">
                <div style="width: {{ $percent }}%; min-width: {{ $option->votes_count > 0 ? '4px' : '0' }}; height: 100%; background: linear-gradient(90deg, #f59e0b, #f97316); border-radius: 6px;"></div>
            </div>
        </div>
    @empty
        <p style="text-align: center; font-size: 13px;">برای این نظرسنجی گزینه‌ای ثبت نشده است.</p>
    @endforelse
</div>
