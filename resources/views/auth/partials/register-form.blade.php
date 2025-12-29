<div class="auth-card" style="max-width: 550px;">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="fas fa-book-reader"></i> Perpuz
        </div>
        <div class="auth-subtitle">Create a new account</div>
    </div>

    <div id="register-error-alert" class="alert alert-danger" style="display: none; background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--danger);">
    </div>

    <form id="register-form">
        <div class="grid grid-cols-2" style="gap: 1rem;">
            <div class="form-group">
                <label class="form-label" for="nim">NIM</label>
                <input id="nim" type="text" class="form-control" name="nim" required placeholder="NIM">
            </div>

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input id="name" type="text" class="form-control" name="name" required placeholder="Full Name">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label" for="register-email">Email Address</label>
            <input id="register-email" type="email" class="form-control" name="email" required placeholder="Email Address">
        </div>

        <div class="grid grid-cols-2" style="gap: 1rem;">
            <div class="form-group">
                <label class="form-label" for="phone">Phone Number</label>
                <input id="phone" type="text" class="form-control" name="phone" placeholder="Phone Number">
            </div>

            <div class="form-group">
                <label class="form-label" for="address">Address</label>
                <input id="address" type="text" class="form-control" name="address" placeholder="Address">
            </div>
        </div>

        <div class="grid grid-cols-2" style="gap: 1rem;">
            <div class="form-group">
                <label class="form-label" for="register-password">Password</label>
                <input id="register-password" type="password" class="form-control" name="password" required placeholder="Password">
            </div>

            <div class="form-group">
                <label class="form-label" for="password-confirm">Confirm Password</label>
                <input id="password-confirm" type="password" class="form-control" name="password_confirmation" required placeholder="Confirm Password">
            </div>
        </div>

        <button type="submit" id="btn-register" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
            Register
        </button>

        <div style="text-align: center; margin-top: 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
            Already have an account? <a href="{{ route('login') }}" id="go-to-login" style="color: var(--accent);">Login</a>
        </div>
    </form>
</div>

<script>
    document.getElementById('register-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const nim = document.getElementById('nim').value;
        const name = document.getElementById('name').value;
        const email = document.getElementById('register-email').value;
        const phone = document.getElementById('phone').value;
        const address = document.getElementById('address').value;
        const password = document.getElementById('register-password').value;
        const password_confirmation = document.getElementById('password-confirm').value;
        
        if (password !== password_confirmation) {
            const errorAlert = document.getElementById('register-error-alert');
            errorAlert.textContent = 'Passwords do not match.';
            errorAlert.style.display = 'block';
            return;
        }
        
        const errorAlert = document.getElementById('register-error-alert');
        const btnRegister = document.getElementById('btn-register');
        
        // Reset error
        errorAlert.style.display = 'none';
        btnRegister.disabled = true;
        btnRegister.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
        
        try {
            const response = await fetch('/api/auth/register', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    nim, name, email, phone, address, password, password_confirmation 
                })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Store token (API returns token in authorization.token)
                localStorage.setItem('auth_token', data.authorization.token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                window.location.href = '/dashboard';
            } else {
                let errorMessage = data.message || 'Registration failed.';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('<br>');
                }
                errorAlert.innerHTML = errorMessage;
                errorAlert.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            errorAlert.textContent = 'An error occurred. Please try again.';
            errorAlert.style.display = 'block';
        } finally {
            btnRegister.disabled = false;
            btnRegister.innerHTML = 'Register';
        }
    });
</script>
