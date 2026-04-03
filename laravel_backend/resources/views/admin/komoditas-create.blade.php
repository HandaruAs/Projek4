@extends('admin.layouts')

@section('title', 'Add Commodity')
@section('page-title', 'Add Commodity')
@section('page-sub', 'Add a new commodity to the SIMOPANG system.')

@section('content')

<div class="table-card" style="max-width:660px">

    <div class="table-header">
        <div>
            <div class="table-title">New Commodity</div>
            <div class="table-subtitle">Fill in the details below to register a new commodity.</div>
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

    <form method="POST" action="/admin/komoditas"
          style="padding:1.5rem; display:flex; flex-direction:column; gap:1.25rem">
        @csrf

        {{-- Commodity Name --}}
        <div class="form-group-admin">
            <label class="form-label-admin">Commodity Name</label>
            <input type="text"
                   name="name"
                   class="form-input-admin"
                   placeholder="e.g. Beras Premium"
                   value="{{ old('name') }}"
                   required>
        </div>

        {{-- Category --}}
        <div class="form-group-admin">
            <label class="form-label-admin">Category</label>
            <select name="category_id" class="form-select" required>
                <option value="">-- Select Category --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}"
                            {{ old('category_id') == $cat->id ? 'selected' : '' }}>
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
                <i class="fas fa-plus"></i> Save Commodity
            </button>
        </div>

    </form>
</div>

@endsection
