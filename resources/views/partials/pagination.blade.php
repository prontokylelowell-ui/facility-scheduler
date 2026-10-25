@if ($paginator->hasPages())
    <nav aria-label="Pagination" style="display: flex; justify-content: space-between; align-items: center; gap: 16px; margin-top: 24px; font-size: 0.85rem; color: var(--text-muted);">
        <div>Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</div>
        <div style="display: flex; align-items: center; gap: 8px;">
            @if ($paginator->onFirstPage())
                <span aria-disabled="true" style="padding: 8px 14px; border: 1px solid var(--border); border-radius: 8px; opacity: 0.55;">Previous</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" style="padding: 8px 14px; border: 1px solid var(--border); border-radius: 8px; color: var(--text-muted); text-decoration: none;">Previous</a>
            @endif

            <span style="padding: 8px 4px; font-weight: 600; color: var(--text-dark);">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" style="padding: 8px 14px; border: 1px solid var(--border); border-radius: 8px; color: var(--text-muted); text-decoration: none;">Next</a>
            @else
                <span aria-disabled="true" style="padding: 8px 14px; border: 1px solid var(--border); border-radius: 8px; opacity: 0.55;">Next</span>
            @endif
        </div>
    </nav>
@endif
