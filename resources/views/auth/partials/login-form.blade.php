<div class="auth-card">
    <div class="auth-header">
        <div class="auth-logo">
            <i class="fas fa-book-reader"></i> Perpuz
        </div>
        <div class="auth-subtitle">Login to your account</div>
    </div>

    <div id="login-error-alert" class="alert alert-danger" style="display: none; background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--danger);">
    </div>

    <form id="login-form">
        <div class="form-group">
            <label class="form-label" for="login-email">Email Address</label>
            <input id="login-email" type="email" class="form-control" name="email" required autocomplete="email" placeholder="Email Address">
        </div>

        <div class="form-group">
            <label class="form-label" for="login-password">Password</label>
            <input id="login-password" type="password" class="form-control" name="password" required autocomplete="current-password" placeholder="Password">
        </div>

        <button type="submit" id="btn-login" class="btn btn-primary" style="width: 100%;">
            Login
        </button>

        <div style="text-align: center; margin-top: 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
            Don't have an account? <a href="{{ route('register') }}" id="go-to-register" style="color: var(--accent);">Register</a>
        </div>
    </form>
</div>

<script>
    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.getElementById('login-email').value;
        const password = document.getElementById('login-password').value;
        const errorAlert = document.getElementById('login-error-alert');
        const btnLogin = document.getElementById('btn-login');
        
        // Reset error
        errorAlert.style.display = 'none';
        btnLogin.disabled = true;
        btnLogin.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Logging in...';
        
        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email, password })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Store token
                localStorage.setItem('auth_token', data.access_token);
                localStorage.setItem('user', JSON.stringify(data.user));
                
                window.location.href = '/dashboard';
            } else {
                errorAlert.textContent = data.error || 'Login failed. Please check your credentials.';
                errorAlert.style.display = 'block';
            }
        } catch (error) {
            console.error('Error:', error);
            errorAlert.textContent = 'An error occurred. Please try again.';
            errorAlert.style.display = 'block';
        } finally {
            btnLogin.disabled = false;
            btnLogin.innerHTML = 'Login';
        }
    });
</script>
