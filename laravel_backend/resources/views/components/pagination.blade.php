@if($paginator->hasPages())
<div class="pagination">
    {{-- Previous --}}
    @if($paginator->onFirstPage())
        <span class="page-btn disabled"><i class="fas fa-chevron-left"></i></span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}" class="page-btn">
            <i class="fas fa-chevron-left"></i>
        </a>
    @endif

    {{-- Page Numbers --}}
    @foreach($paginator->getUrlRange(
        max(1, $paginator->currentPage() - 2),
        min($paginator->lastPage(), $paginator->currentPage() + 2)
    ) as $page => $url)
        @if($page == $paginator->currentPage())
            <span class="page-btn active">{{ $page }}</span>
        @else
            <a href="{{ $url }}" class="page-btn">{{ $page }}</a>
        @endif
    @endforeach

    {{-- Next --}}
    @if($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}" class="page-btn">
            <i class="fas fa-chevron-right"></i>
        </a>
    @else
        <span class="page-btn disabled"><i class="fas fa-chevron-right"></i></span>
    @endif
</div>
@endif
