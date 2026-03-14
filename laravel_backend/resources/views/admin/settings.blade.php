@extends('layouts.admin')

@section('title', 'Settings')
@section('page-title', 'Settings')
@section('page-sub', 'Manage your account settings and system configuration.')

@section('content')

<div style="display:grid; grid-template-columns:1fr 1fr; gap:20px">

    {{-- PROFILE --}}
    <div class="card">
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
    <div class="card">
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

    {{-- SYSTEM INFO --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fas fa-server" style="color:var(--green); margin-right:8px"></i>
                System Information
            </div>
        </div>
        <div class="card-body">
            @php
                $info = [
                    ['label' => 'App Version',   'value' => 'SIMOPANG v1.0.0'],
                    ['label' => 'Framework',      'value' => 'Laravel 12.x'],
                    ['label' => 'Database',       'value' => 'MongoDB 7.x'],
                    ['label' => 'PHP Version',    'value' => phpversion()],
                    ['label' => 'Server Time',    'value' => now()->format('M d, Y — H:i') . ' WIB'],
                    ['label' => 'Environment',    'value' => ucfirst(app()->environment())],
                    ['label' => 'Total Records',  'value' => '12,450 data points'],
                    ['label' => 'Total Regions',  'value' => '18 daerah'],
                ];
            @endphp
            @foreach($info as $item)
            <div style="display:flex; justify-content:space-between; align-items:center; padding:10px 0; border-bottom:1px solid var(--border)">
                <span style="font-size:13px; font-weight:600; color:var(--text-secondary)">{{ $item['label'] }}</span>
                <span style="font-size:13px; color:var(--text-primary); font-weight:500">{{ $item['value'] }}</span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- DANGER ZONE --}}
    <div class="card" style="border-color:#fecaca">
        <div class="card-header" style="background:#fef2f2; border-color:#fecaca">
            <div class="card-title" style="color:var(--red)">
                <i class="fas fa-triangle-exclamation" style="margin-right:8px"></i>
                Danger Zone
            </div>
        </div>
        <div class="card-body">
            <div style="display:flex; flex-direction:column; gap:16px">
                <div style="padding:14px; background:#fef2f2; border:1px solid #fecaca; border-radius:10px">
                    <div style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:4px">Reset All Prediction Data</div>
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:12px">Delete all stored prediction data. This action cannot be undone.</div>
                    <button class="btn-danger" style="width:100%; justify-content:center">
                        <i class="fas fa-trash"></i> Reset Prediction Data
                    </button>
                </div>
                <div style="padding:14px; background:#fef2f2; border:1px solid #fecaca; border-radius:10px">
                    <div style="font-size:13px; font-weight:700; color:var(--text-primary); margin-bottom:4px">Logout All Devices</div>
                    <div style="font-size:12px; color:var(--text-muted); margin-bottom:12px">Invalidate all active sessions across all logged-in devices.</div>
                    <button class="btn-danger" style="width:100%; justify-content:center">
                        <i class="fas fa-right-from-bracket"></i> Logout All Devices
                    </button>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection