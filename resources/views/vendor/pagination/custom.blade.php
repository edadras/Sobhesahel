@if ($paginator->hasPages())
    <div class="paginationBx">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="btn disabled transitionCls">
                <span class="icon-Group-2211"></span>
            </button>
        @else
            <button type="button" class="btn transitionCls" onclick="window.location='{{ $paginator->previousPageUrl() }}'">
                <span class="icon-Group-2211"></span>
            </button>
        @endif

        <ul>
            {{-- Pagination Elements --}}
            @php
                // Define how many pages to display on either side of the current page
                $start = max($paginator->currentPage() - 2, 1);
                $end = min($paginator->currentPage() + 2, $paginator->lastPage());
            @endphp   

            {{-- "First Page" Link --}}
            @if ($start > 1)
                <li><a href="{{ $paginator->url(1) }}" class="transitionCls">1</a></li>
                @if ($start > 2)
                    <li><span>...</span></li>
                @endif
            @endif

            {{-- Display pages within the calculated range --}}
            @for ($page = $start; $page <= $end; $page++)
                @if ($page == $paginator->currentPage())
                    <li><a href="#" class="transitionCls active">{{ $page }}</a></li>
                @else
                    <li><a href="{{ $paginator->url($page) }}" class="transitionCls">{{ $page }}</a></li>
                @endif
            @endfor

            {{-- "Last Page" Link --}}
            @if ($end < $paginator->lastPage())
                @if ($end < $paginator->lastPage() - 1)
                    <li><span>...</span></li>
                @endif
                <li><a href="{{ $paginator->url($paginator->lastPage()) }}" class="transitionCls">{{ $paginator->lastPage() }}</a></li>
            @endif
        </ul>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button type="button" class="btn transitionCls" onclick="window.location='{{ $paginator->nextPageUrl() }}'">
                <span class="icon-Group-2210"></span>
            </button>
        @else
            <button type="button" class="btn disabled transitionCls">
                <span class="icon-Group-2210"></span>
            </button>
        @endif
    </div>
@endif
