<div class="filter-bar">
    {{-- Search --}}
    <div class="search-box" style="flex:1">
        <i class="fas fa-magnifying-glass"></i>
        <input type="text"
               id="{{ $searchId ?? 'searchInput' }}"
               name="search"
               value="{{ request('search') }}"
               placeholder="{{ $placeholder ?? 'Search...' }}"
               autocomplete="off">
    </div>
    {{-- Category filter --}}
    @if(isset($categories))
        <span class="filter-label">Category:</span>
        <select class="form-select"
                id="{{ $categoryId ?? 'categoryFilter' }}"
                name="category"
                style="width:160px">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                @php
                    $catValue = is_string($cat) ? $cat : (string) $cat->_id;
                    $catLabel = is_string($cat) ? $cat : $cat->name;
                @endphp
                <option value="{{ $catValue }}"
                        {{ request('category') == $catValue ? 'selected' : '' }}>
                    {{ $catLabel }}
                </option>
            @endforeach
        </select>
    @endif
    {{-- Date filter --}}
    @if($withDate ?? false)
        <span class="filter-label">Date:</span>
        <input type="date"
               id="{{ $dateId ?? 'dateFilter' }}"
               name="date"
               value="{{ request('date') }}"
               class="form-input-admin"
               style="width:160px">
    @endif
    {{-- Slot untuk tombol tambahan --}}
    {{ $slot }}
</div>
{{-- Auto submit — no button needed --}}
<script>
(function () {
    const form = document.currentScript.closest('form');
    if (!form) return;
    // Category & date → submit langsung
    form.querySelectorAll('select, input[type=date]').forEach(el => {
        el.addEventListener('change', () => form.submit());
    });
    // Search text → submit setelah berhenti ketik 500ms
    const searchEl = form.querySelector('input[type=text]');
    if (searchEl) {
        let timer;
        searchEl.addEventListener('input', () => {
            clearTimeout(timer);
            timer = setTimeout(() => form.submit(), 500);
        });
    }
})();
</script>