@if ($paginator->hasPages())
    <div class="paginationBx">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <button type="button" class="btn disabled transitionCls">
                <span class="icon-Group-2210"></span>
            </button>
        @else
            <button type="button" class="btn transitionCls" onclick="window.location='{{ $paginator->previousPageUrl() }}'">
                <span class="icon-Group-2210"></span>
            </button>
        @endif

        {{-- Pagination Info --}}
        <div>
            {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }}
        </div>

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <button type="button" class="btn transitionCls" onclick="window.location='{{ $paginator->nextPageUrl() }}'">
                <span class="icon-Group-2211"></span>
            </button>
        @else
            <button type="button" class="btn disabled transitionCls">
                <span class="icon-Group-2211"></span>
            </button>
        @endif
    </div>
@endif
