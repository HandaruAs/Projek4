@extends('admin.layouts')

@section('title', 'Kelola Komoditas')
@section('page-title', 'Kelola Komoditas')
@section('page-sub', 'Manage commodity data available in the SIMOPANG monitoring system.')

@section('content')

@if(session('success'))
<div class="alert-success">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- STAT CARDS --}}
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Commodities</div>
            <div class="stat-value">{{ $totalKomoditas }}</div>
            <div class="stat-change up">
                <i class="fas fa-boxes-stacked"></i>
                <span class="stat-change-sub">registered</span>
            </div>
        </div>
        <div class="stat-icon icon-orange"><i class="fas fa-boxes-stacked"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Active Commodities</div>
            <div class="stat-value">{{ $activeKomoditas }}</div>
            <div class="stat-change up">
                <i class="fas fa-circle-check"></i>
                <span class="stat-change-sub">have price data</span>
            </div>
        </div>
        <div class="stat-icon icon-green"><i class="fas fa-circle-check"></i></div>
    </div>
    <div class="stat-card">
        <div>
            <div class="stat-label">Total Categories</div>
            <div class="stat-value">{{ $totalCategories }}</div>
            <div class="stat-change neutral">
                <i class="fas fa-minus"></i>
                <span class="stat-change-sub">no changes</span>
            </div>
        </div>
        <div class="stat-icon icon-blue"><i class="fas fa-layer-group"></i></div>
    </div>
</div>

{{-- FILTER + ADD --}}
<form method="GET" action="/admin/komoditas">
    <x-filter-bar
        placeholder="Search commodity name..."
        :categories="$categoryList"
        search-id="searchInput"
        category-id="categoryFilter">
        <a href="/admin/komoditas/create" class="btn-primary" style="white-space:nowrap">
            <i class="fas fa-plus"></i> Add Commodity
        </a>
    </x-filter-bar>
</form>

{{-- TABLE --}}
<div class="table-card">
    <div class="table-header">
        <div>
            <div class="table-title">Commodity List</div>
            <div class="table-subtitle">
                All commodities registered in the SIMOPANG system.
                @if(request('search') || request('category'))
                    —
                    <a href="/admin/komoditas"
                       style="font-size:11.5px; color:var(--accent); margin-left:4px">
                        <i class="fas fa-xmark"></i> Clear filter
                    </a>
                @endif
            </div>
        </div>
    </div>

    <table id="commodityTable">
        <thead>
            <tr>
                <th>#</th>
                <th>Commodity Name</th>
                <th>Category</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($commodities as $index => $item)
            <tr>
                <td class="date-text">{{ $commodities->firstItem() + $index }}</td>

                <td class="commodity-name">{{ $item->name }}</td>

                <td>
                    @if(!empty($item->category))
                        <span class="region-text">{{ $item->category }}</span>
                    @else
                        <span class="date-text">—</span>
                    @endif
                </td>

                <td>
                    <div class="action-group">
                        <a href="/admin/komoditas/{{ $item->id }}/edit" class="btn-action-edit">
                            <i class="fas fa-pen"></i> Edit
                        </a>
                        <form method="POST" action="/admin/komoditas/{{ $item->id }}"
                              onsubmit="return confirm('Hapus komoditas ini?')"
                              style="display:inline; margin:0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-action-delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:2.5rem; color:var(--text-muted)">
                    <i class="fas fa-box-open"
                       style="font-size:1.5rem; display:block; margin-bottom:.5rem; opacity:.4"></i>
                    @if(request('search') || request('category'))
                        Tidak ada komoditas yang cocok dengan filter.
                    @else
                        Belum ada data komoditas.
                    @endif
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="table-footer">
        <span class="table-footer-text">
            Showing {{ $commodities->firstItem() }}–{{ $commodities->lastItem() }}
            of {{ $commodities->total() }} commodities
        </span>
        <x-pagination :paginator="$commodities" />
    </div>
</div>

@endsection
