@extends('layouts.app')

@section('content')
<div class="grid grid-cols-1">
    <div style="margin-bottom: 2rem;">
        <h1 style="font-size: 1.8rem; margin-bottom: 0.25rem;">My Profile</h1>
        <p style="color: var(--text-secondary);">Manage your account information</p>
    </div>

    <div class="grid grid-cols-3" style="gap: 2rem; align-items: start;">
        <!-- Profile Card -->
        <div class="card" style="text-align: center;">
            <div style="width: 100px; height: 100px; background: var(--bg-primary); border-radius: 50%; margin: 0 auto 1.5rem; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; color: var(--accent); border: 2px solid var(--border-color);">
                <i class="fas fa-user"></i>
            </div>
            <h2 id="profile-name-display" style="margin-bottom: 0.25rem;">User Name</h2>
            <p id="profile-role-display" style="color: var(--accent); font-size: 0.9rem; margin-bottom: 1.5rem;">Student Member</p>
            
            <div style="text-align: left; background: var(--bg-primary); padding: 1rem; border-radius: 8px;">
                <div style="margin-bottom: 0.75rem;">
                    <span style="display: block; font-size: 0.8rem; color: var(--text-secondary);">NIM</span>
                    <span id="profile-nim-display" style="font-weight: 600;">-</span>
                </div>
                <div>
                    <span style="display: block; font-size: 0.8rem; color: var(--text-secondary);">Member Since</span>
                    <span id="profile-joined-display" style="font-weight: 600;">-</span>
                </div>
            </div>
        </div>
        
        <!-- Edit Form -->
        <div class="card" style="grid-column: span 2;">
            <div id="success-alert" class="alert alert-success" style="display: none; background: rgba(16, 185, 129, 0.1); color: var(--success); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--success);">
                Profile updated successfully.
            </div>
            
            <form id="profile-form">
                <div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input id="name" type="text" class="form-control" name="name" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input id="email" type="email" class="form-control" name="email" required>
                    </div>
                </div>
                
                <div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number</label>
                        <input id="phone" type="text" class="form-control" name="phone">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="address">Address</label>
                        <input id="address" type="text" class="form-control" name="address">
                    </div>
                </div>
                
                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 2rem 0;">
                
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem;">Change Password <span style="font-size: 0.9rem; color: var(--text-secondary); font-weight: 400;">(Leave empty to keep current)</span></h3>
                
                <div class="grid grid-cols-2" style="gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <input id="password" type="password" class="form-control" name="password">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <input id="password_confirmation" type="password" class="form-control" name="password_confirmation">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary" style="min-width: 150px;">
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
