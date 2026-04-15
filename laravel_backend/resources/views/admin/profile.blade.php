@extends('layouts.layout')

@section('content')

@if(session('success'))
<div style="margin-bottom:1rem; padding:.75rem 1rem; background:#d1fae5; color:#065f46;
            border:1px solid #a7f3d0; border-radius:10px; font-size:13px;
            display:flex; align-items:center; gap:8px;">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

@if($errors->any())
<div style="margin-bottom:1rem; padding:.75rem 1rem; background:#fef2f2; color:#991b1b;
            border:1px solid #fecaca; border-radius:10px; font-size:13px;
            display:flex; align-items:center; gap:8px;">
    <i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}
</div>
@endif

<div class="card" style="max-width:600px; margin:0 auto">
    <div class="card-header">
        <div class="card-title">
            <i class="fas fa-user-circle" style="color:var(--accent); margin-right:8px"></i>
            Account Profile
        </div>
    </div>
    <div class="card-body">

        {{-- Avatar Preview --}}
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:22px; padding-bottom:18px; border-bottom:1px solid var(--border)">
            <div id="avatarWrapper"
                 onclick="openAvatarModal()"
                 style="width:64px; height:64px; border-radius:50%; overflow:hidden; flex-shrink:0;
                        background:linear-gradient(135deg,#3b82f6,#8b5cf6);
                        display:flex; align-items:center; justify-content:center;
                        cursor:pointer; position:relative; transition:opacity 0.2s"
                 onmouseover="this.style.opacity='0.8'"
                 onmouseout="this.style.opacity='1'"
                 title="Click to view photo">
                @if($user['avatar'])
                    <img id="avatarPreview" src="{{ asset('storage/' . $user['avatar']) }}"
                         style="width:100%; height:100%; object-fit:cover">
                @else
                    <span id="avatarInitial" style="font-size:22px; font-weight:700; color:#fff">
                        {{ strtoupper(substr($user['nama'] ?? 'A', 0, 1)) }}
                    </span>
                @endif
            </div>
            <div>
                <div style="font-weight:700; color:var(--text-primary); font-size:15px">{{ $user['nama'] ?? 'Admin User' }}</div>
                <div style="font-size:12.5px; color:var(--text-muted); margin-top:3px">{{ $user['email'] ?? '' }}</div>
                <span class="badge badge-blue" style="margin-top:6px">{{ ucfirst($user['role'] ?? 'admin') }}</span>
            </div>
        </div>

        <form method="POST" action="/admin/settings/profile" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div style="display:flex; flex-direction:column; gap:14px">

                {{-- Avatar Upload --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">Profile Photo <span style="color:var(--text-muted); font-weight:400; font-size:11.5px">(optional)</span></label>
                    <input type="file" name="avatar" id="avatarInput"
                           accept="image/jpg,image/jpeg,image/png,image/webp"
                           class="form-input-admin"
                           style="padding:6px 10px; cursor:pointer">
                    <span style="font-size:11.5px; color:var(--text-muted); margin-top:4px; display:block">
                        <i class="fas fa-info-circle" style="font-size:10px"></i>
                        Max 2MB. Format: JPG, PNG, WEBP
                    </span>
                </div>

                {{-- Full Name --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">Full Name</label>
                    <input type="text" class="form-input-admin" name="name"
                           value="{{ old('name', $user['nama'] ?? '') }}"
                           placeholder="Enter full name" required>
                </div>

                {{-- Email --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">Email Address</label>
                    <input type="email" class="form-input-admin" name="email"
                           value="{{ old('email', $user['email'] ?? '') }}"
                           placeholder="Enter email" required>
                </div>

                {{-- Phone --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">
                        Phone Number
                        <span style="color:var(--text-muted); font-weight:400; font-size:11.5px">(optional)</span>
                    </label>
                    <input type="text" class="form-input-admin" name="phone"
                           value="{{ old('phone', $user['phone'] ?? '') }}"
                           placeholder="e.g. 08123456789">
                </div>

                {{-- Address --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">
                        Address
                        <span style="color:var(--text-muted); font-weight:400; font-size:11.5px">(optional)</span>
                    </label>
                    <textarea name="address" class="form-input-admin" rows="3"
                              placeholder="Enter your address...">{{ old('address', $user['address'] ?? '') }}</textarea>
                </div>

                {{-- Role (disabled) --}}
                <div class="form-group-admin">
                    <label class="form-label-admin">Role</label>
                    <input type="text" class="form-input-admin"
                           value="{{ ucfirst($user['role'] ?? 'admin') }}" disabled
                           style="cursor:not-allowed; color:var(--text-muted)">
                </div>

            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-floppy-disk"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Preview Avatar --}}
<div id="avatarModal" onclick="closeAvatarModal()"
     style="display:none; position:fixed; inset:0; z-index:9999;
            background:rgba(15,23,42,0.7); backdrop-filter:blur(6px);
            align-items:center; justify-content:center; cursor:zoom-out">
    <div onclick="event.stopPropagation()"
         style="position:relative; max-width:420px; width:90%">
        <img id="avatarModalImg" src=""
             style="width:100%; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.4); display:block">
        <button onclick="closeAvatarModal()"
                style="position:absolute; top:-14px; right:-14px; width:32px; height:32px;
                       border-radius:50%; border:none; background:#fff; cursor:pointer;
                       font-size:14px; color:#64748b; box-shadow:0 2px 8px rgba(0,0,0,0.15);
                       display:flex; align-items:center; justify-content:center">
            <i class="fas fa-xmark"></i>
        </button>
    </div>
</div>

<script>
// Preview avatar sebelum upload
document.getElementById('avatarInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(ev) {
        const wrapper = document.getElementById('avatarWrapper');
        wrapper.innerHTML = `<img src="${ev.target.result}"
            style="width:100%; height:100%; object-fit:cover; border-radius:50%">`;
        document.getElementById('avatarModalImg').src = ev.target.result;
    };
    reader.readAsDataURL(file);
});

// Buka modal lihat foto
function openAvatarModal() {
    const img = document.querySelector('#avatarWrapper img');
    if (!img) return;

    document.getElementById('avatarModalImg').src = img.src;
    const modal = document.getElementById('avatarModal');
    modal.style.display = 'flex';
}

// Tutup modal
function closeAvatarModal() {
    document.getElementById('avatarModal').style.display = 'none';
}

// Tutup dengan Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeAvatarModal();
});
</script>

@endsection