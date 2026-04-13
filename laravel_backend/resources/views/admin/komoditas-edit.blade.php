@extends('layouts.layout')

@section('title', 'Edit Commodity')
@section('page-title', 'Edit Commodity')
@section('page-sub', 'Update commodity information.')

@section('content')

<div class="table-card" style="max-width:640px">
    <div class="table-header">
        <div>
            <div class="table-title">Edit: {{ $commodity->name }}</div>
            <div class="table-subtitle">Update the commodity name and category.</div>
        </div>
    </div>

    @if($errors->any())
    <div style="margin:1rem 1.5rem 0; padding:.75rem 1rem;
                background:#fef2f2; color:#991b1b;
                border:1px solid #fecaca; border-radius:10px;
                font-size:13px; display:flex; align-items:center; gap:8px;">
        <i class="fas fa-circle-exclamation"></i>
        {{ $errors->first() }}
    </div>
    @endif

    <form method="POST" action="/admin/komoditas/{{ $commodity->id }}"
          style="padding:1.5rem; display:flex; flex-direction:column; gap:1.25rem">
        @csrf
        @method('PUT')

        {{-- Commodity Name --}}
        <div class="form-group-admin">
            <label class="form-label-admin">Commodity Name</label>
            <input type="text"
                   name="name"
                   class="form-input-admin"
                   value="{{ old('name', $commodity->name) }}"
                   required>
        </div>

        {{-- Category --}}
        <div class="form-group-admin">
            <label class="form-label-admin">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                        {{ old('category_id', $commodity->category) == $cat->name ? 'selected' : '' }}>
                        {{ $cat->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Actions --}}
        <div class="form-actions">
            <a href="/admin/komoditas" class="btn-secondary">
                <i class="fas fa-xmark"></i> Cancel
            </a>
            <button type="submit" class="btn-primary">
                <i class="fas fa-floppy-disk"></i> Update Commodity
            </button>
        </div>
    </form>
</div>

@endsection
