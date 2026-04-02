@extends('admin.layouts')

@section('content')

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start">

    {{-- PROFILE --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-user-circle" style="color:var(--accent); margin-right:8px"></i>
                Account Profile
            </div>
        </div>
        <div class="card-body">
            <div style="display:flex; align-items:center; gap:16px; margin-bottom:22px; padding-bottom:18px; border-bottom:1px solid var(--border)">
                <div style="width:58px; height:58px; border-radius:50%; background:linear-gradient(135deg,#3b82f6,#8b5cf6); display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:700; color:#fff; flex-shrink:0">
                    {{ strtoupper(substr($user->name ?? 'A', 0, 1)) }}
                </div>
                <div>
                    <div style="font-weight:700; color:var(--text-primary); font-size:15px">{{ $user->name ?? 'Admin User' }}</div>
                    <div style="font-size:12.5px; color:var(--text-muted); margin-top:3px">{{ $user->email ?? 'admin@simopang.id' }}</div>
                    <span class="badge badge-blue" style="margin-top:6px">{{ ucfirst($user->role ?? 'admin') }}</span>
                </div>
            </div>
            <form method="POST" action="/admin/settings/profile">
                @csrf @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px">
                    <div class="form-group-admin">
                        <label class="form-label-admin">Full Name</label>
                        <input type="text" class="form-input-admin" name="name"
                               value="{{ $user->name ?? '' }}" placeholder="Enter full name">
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Email Address</label>
                        <input type="email" class="form-input-admin" name="email"
                               value="{{ $user->email ?? '' }}" placeholder="Enter email">
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Role</label>
                        <input type="text" class="form-input-admin"
                               value="{{ ucfirst($user->role ?? 'admin') }}" disabled
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

    {{-- CHANGE PASSWORD --}}
    <div class="card" style="margin-bottom:0">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-lock" style="color:var(--orange); margin-right:8px"></i>
                Change Password
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="/admin/settings/password">
                @csrf @method('PUT')
                <div style="display:flex; flex-direction:column; gap:14px">
                    <div class="form-group-admin">
                        <label class="form-label-admin">Current Password</label>
                        <input type="password" class="form-input-admin" name="current_password"
                               placeholder="Enter current password">
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">New Password</label>
                        <input type="password" class="form-input-admin" name="new_password"
                               placeholder="Enter new password">
                    </div>
                    <div class="form-group-admin">
                        <label class="form-label-admin">Confirm New Password</label>
                        <input type="password" class="form-input-admin" name="new_password_confirmation"
                               placeholder="Repeat new password">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection