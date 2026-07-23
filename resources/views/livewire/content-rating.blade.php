<div class="contentRatingBx text-center" dir="rtl" style="margin: 20px 0; padding: 15px; border: 1px solid #eee; border-radius: 8px;">
    <p style="margin-bottom: 8px; font-weight: bold;">به این مطلب امتیاز دهید:</p>

    <div style="direction: ltr; line-height: 1;">
        @for($i = 1; $i <= 5; $i++)
            <button type="button"
                    wire:click="rate({{ $i }})"
                    wire:loading.attr="disabled"
                    @if(!is_null($myRating)) disabled @endif
                    title="{{ \Morilog\Jalali\CalendarUtils::convertNumbers((string) $i) }} ستاره"
                    style="background: none; border: none; padding: 2px 4px; font-size: 28px; cursor: {{ is_null($myRating) ? 'pointer' : 'default' }}; color: {{ $i <= round($average) ? '#f5b301' : '#cfcfcf' }};">
                &#9733;
            </button>
        @endfor
    </div>

    <small style="display: block; margin-top: 6px; color: #666;">
        @if($count > 0)
            میانگین {{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($average, 1)) }} از ۵
            ({{ \Morilog\Jalali\CalendarUtils::convertNumbers(number_format($count)) }} رأی)
        @else
            هنوز امتیازی ثبت نشده است؛ اولین نفر باشید!
        @endif
    </small>

    @if(!is_null($myRating))
        <small style="display: block; margin-top: 4px; color: #2e7d32;">
            امتیاز شما: {{ \Morilog\Jalali\CalendarUtils::convertNumbers((string) $myRating) }} از ۵
        </small>
    @endif
</div>
