@extends('layouts.app')

@section('content')
<div class="dashboard-content">
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account information</p>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem; align-items: start;">
        <!-- Profile Card -->
        <div class="profile-card">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
            <h2 id="profile-name-display" class="profile-name">User Name</h2>
            <p id="profile-role-display" class="profile-role">Student Member</p>
            
            <div class="profile-meta">
                <div class="profile-meta-item">
                    <span class="profile-meta-label">NIM</span>
                    <span id="profile-nim-display" class="profile-meta-value">-</span>
                </div>
                <div class="profile-meta-item">
                    <span class="profile-meta-label">Member Since</span>
                    <span id="profile-joined-display" class="profile-meta-value">-</span>
                </div>
            </div>
        </div>
        
        <!-- Edit Form --><div class="profile-form-card">
            <div id="success-alert" class="success-alert" style="display: none;">
                Profile updated successfully.
            </div>
            
            <form id="profile-form">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input id="name" type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input id="email" type="email" class="form-control" name="email" required>
                    </div>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input id="phone" type="text" class="form-control" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="address">Address</label>
                        <input id="address" type="text" class="form-control" name="address">
                    </div>
                </div>
                
                <hr style="border: 0; border-top: 1px solid #e5e7eb; margin: 2rem 0;">
                
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: var(--text-new);">Change Password <span style="font-size: 0.9rem; color: #6b7280; font-weight: 400;">(Leave empty to keep current)</span></h3>
                
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input id="password" type="password" class="form-control" name="password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation">
                    </div>
                </div>
                
                <button type="submit" class="dashboard-btn-primary" style="min-width: 150px;">
                    Save Changes
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        loadProfile();
        
        document.getElementById('profile-form').addEventListener('submit', updateProfile);
    });
    
    async function loadProfile() {
        try {
            // First check local storage for speed
            const cachedUser = JSON.parse(localStorage.getItem('user') || '{}');
            if (cachedUser.id) fillForm(cachedUser);
            
            // Then fetch fresh data
            const response = await fetch('/api/member/profile', {
                headers: { 'Authorization': 'Bearer ' + localStorage.getItem('auth_token') }
            });
            const data = await response.json();
            
            if (data.success) {
                fillForm(data.data);
                // Update local storage
                localStorage.setItem('user', JSON.stringify(data.data));
            }
        } catch(e) { console.error(e); }
    }
    
    function fillForm(user) {
        document.getElementById('name').value = user.name || '';
        document.getElementById('email').value = user.email || '';
        document.getElementById('phone').value = user.phone || '';
        document.getElementById('address').value = user.address || '';
        
        document.getElementById('profile-name-display').textContent = user.name || 'User';
        document.getElementById('profile-nim-display').textContent = user.nim || '-';
        document.getElementById('profile-joined-display').textContent = new Date(user.created_at).toLocaleDateString();
    }
    
    async function updateProfile(e) {
        e.preventDefault();
        
        const btn = e.target.querySelector('button[type="submit"]');
        const alert = document.getElementById('success-alert');
        
        btn.disabled = true;
        btn.innerHTML = 'Saving...';
        alert.style.display = 'none';
        
        const formData = new FormData(e.target);
        const data = Object.fromEntries(formData.entries());
        
        // Remove empty password fields
        if (!data.password) {
            delete data.password;
            delete data.password_confirmation;
        }
        
        try {
            const response = await fetch('/api/member/profile', {
                method: 'PUT', // Route defined as PUT
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': 'Bearer ' + localStorage.getItem('auth_token'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert.style.display = 'block';
                localStorage.setItem('user', JSON.stringify(result.data));
                fillForm(result.data);
                
                // Clear passwords
                document.getElementById('password').value = '';
                document.getElementById('password_confirmation').value = '';
                
                // Update header via global event or reload (for simplicity here we let it be until refresh/nav)
                document.getElementById('user-name').textContent = result.data.name;
                document.getElementById('user-avatar').textContent = result.data.name.charAt(0).toUpperCase();

            } else {
                let msg = result.message || 'Update failed';
                if (result.errors) msg = Object.values(result.errors).join('\n');
                window.alert(msg);
            }
        } catch (error) {
            console.error(error);
            window.alert('An error occurred');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Save Changes';
        }
    }
</script>
@endpush
