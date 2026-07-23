<div class="sideBox" id="poll" data-lang="fa">
    <div class="head">
        <span></span>
        <p>نظرسنجی</p>
    </div>
    <div class="sideSurvey">
        <div class="sideQues text-end">
            {{ $poll->question }}
        </div>
        <form>
            @if($isEnded ?? false)
                <div class="alert alert-warning text-center" style="font-size: 13px;">
                    این نظرسنجی پایان یافته است
                </div>
            @endif
            @foreach($poll->options as $item)
                <div class="form-check mb-2">
                    <input wire:model="selectedOption" value="{{ $item->id }}" class="form-check-input" type="radio" name="vote"
                           id="option-{{ $item->id }}" @if($isEnded ?? false) disabled @endif>
                    <label class="form-check-label" for="option-{{ $item->id }}">
                        {{ $item->option_text }}
                        <span wire:loading.remove wire:target="fetchResults">
                            @if(isset($results[$item->id]))
                                <span style="color:darkred">({{ number_format($results[$item->id]) . ' ' . 'رای' }})</span>
                            @endif
                        </span>
                    </label>
                </div>
            @endforeach
            <div class="sidSrvryBtn">
                @unless($isEnded ?? false)
                    <button type="button" wire:click="vote" class="btn transitionCls">ثبت</button>
                @endunless
                <button type="button" wire:click="fetchResults" class="btn transitionCls">دیدن نتایج</button>
            </div>
        </form>
    </div>
</div>
