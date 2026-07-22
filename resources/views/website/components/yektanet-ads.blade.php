@foreach(\App\Models\YektanetAd::getForPosition($position) as $ad)
    <div class="yektanet-ad yektanet-ad--{{ $position }}" data-yektanet-ad="{{ $ad->id }}">
        {!! $ad->code !!}
    </div>
@endforeach
