@if ($paginator->hasPages())
    <ul class="flex flex-row list-reset">
        {{-- Previous Page Link --}}
        @if ( ! $paginator->onFirstPage())
            <li><a class="px-1 mx-1 py-2 text-grey-dark no-underline" href="{{ $paginator->previousPageUrl() }}" rel="prev">&larr;</a></li>
        @endif

        @if($paginator->currentPage() > 3)
            <li class="md:block hidden"><a class="px-1 mx-1 py-2 text-grey-dark no-underline" href="{{ $paginator->url(1) }}">1</a></li>
        @endif
        @if($paginator->currentPage() > 4)
            <li class="md:block hidden"><span class="px-1 mx-1 py-2 text-grey-dark">...</span></li>
        @endif
        @foreach(range(1, $paginator->lastPage()) as $i)
            @if($i >= $paginator->currentPage() - 2 && $i <= $paginator->currentPage() + 2)
                @if ($i == $paginator->currentPage())
                    <li><span class="px-1 mx-1 py-2 text-black no-underline font-extrabold">{{ $i }}</span></li>
                @else
                    <li><a class="px-1 mx-1 py-2 text-grey-dark no-underline" href="{{ $paginator->url($i) }}">{{ $i }}</a></li>
                @endif
            @endif
        @endforeach
        @if($paginator->currentPage() < $paginator->lastPage() - 3)
            <li class="md:block hidden"><span class="px-1 mx-1 py-2 text-grey-dark">...</span></li>
        @endif
        @if($paginator->currentPage() < $paginator->lastPage() - 2)
            <li class="md:block hidden"><a class="px-1 mx-1 py-2 text-grey-dark no-underline" href="{{ $paginator->url($paginator->lastPage()) }}">{{ $paginator->lastPage() }}</a></li>
        @endif

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li><a class="px-1 mx-1 py-2 text-grey-dark no-underline" href="{{ $paginator->nextPageUrl() }}" rel="next">&rarr;</a></li>
        @endif

    </ul>
@endif
