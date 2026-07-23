@php
    try {
        $footYear = \Morilog\Jalali\Jalalian::now()->format('Y');
    } catch (\Throwable) {
        $footYear = '';
    }
@endphp
<footer class="foot">
    <div class="foot-inner">
        <div class="brand-word">صبح ساحل</div>
        <span>پایگاه خبری استان هرمزگان — بندرعباس</span>
        <span style="margin-inline-start:auto">© {{ $footYear }} تمامی حقوق محفوظ است</span>
    </div>
</footer>
