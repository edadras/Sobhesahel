<div>
    @if($currentNews)
        <div class="floutNews">
            <div>
                <i></i>
                <strong>خـبر فوری:</strong>
            </div>
            <a id="breaking-news-link" href="{{$currentNews['url']}}">
                <p id="breaking-news-text" class="typing-animation" style="color: inherit;">
                    {{ $currentNews['title'] }}
                </p>
            </a>
            <span class="icon-Group-2168 clsFloutNws"></span>
        </div>
        @endif

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function switchNews() {
            Livewire.dispatch('refreshBreakingNews');
        }
        setInterval(switchNews, 6000);

        // Trigger the first news update immediately
        Livewire.dispatch('refreshBreakingNews');
    });
</script>
