<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }} - Login</title>
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <div class="auth-logo">
                    <i class="fas fa-book-reader"></i> Perpuz
                </div>
                <div class="auth-subtitle">Login to your account</div>
            </div>

            <div id="error-alert" class="alert alert-danger" style="display: none; background: rgba(239, 68, 68, 0.1); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid var(--danger);">
            </div>

            <form id="login-form">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input id="email" type="email" class="form-control" name="email" required autocomplete="email" autofocus>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input id="password" type="password" class="form-control" name="password" required autocomplete="current-password">
                </div>

                <button type="submit" id="btn-login" class="btn btn-primary" style="width: 100%;">
                    Login
                </button>

                <div style="text-align: center; margin-top: 1.5rem; color: var(--text-secondary); font-size: 0.9rem;">
                    Don't have an account? <a href="{{ route('register') }}" style="color: var(--accent);">Register</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const errorAlert = document.getElementById('error-alert');
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
                    
                    // Specific logic for setting cookie if needed for hybrid apps, 
                    // but here we rely on localStorage for API calls.
                    // However, protecting /dashboard route in web is checking session (which we don't have)
                    // Since we are building SPA-like behavior with Blade, we treat Blade views as static shells
                    // and protect them via JS redirection if no token found.
                    
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
        
        // Check if already logged in
        if (localStorage.getItem('auth_token')) {
            window.location.href = '/dashboard';
        }
    </script>
</body>
</html>
