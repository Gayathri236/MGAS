<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Microgreens Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #10B981 0%, #059669 50%, #047857 100%);
            padding: 20px;
        }
        
        .login-container {
            display: flex;
            background: white;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            max-width: 1000px;
            width: 100%;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            padding: 60px 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .login-left::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: pulse 8s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .login-left .leaf-icon {
            font-size: 80px;
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .login-left h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            text-align: center;
        }
        
        .login-left p {
            font-size: 16px;
            opacity: 0.9;
            text-align: center;
            max-width: 300px;
        }
        
        .leaf-decoration {
            position: absolute;
            opacity: 0.1;
            font-size: 150px;
        }
        
        .leaf-1 { top: 10%; left: 10%; transform: rotate(-20deg); }
        .leaf-2 { bottom: 10%; right: 10%; transform: rotate(30deg); }
        
        .login-right {
            flex: 1;
            padding: 60px 50px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-right h2 {
            font-size: 28px;
            color: #1F2937;
            margin-bottom: 10px;
        }
        
        .login-right .subtitle {
            color: #6B7280;
            margin-bottom: 40px;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            color: #374151;
            font-size: 14px;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 18px;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 16px 16px 16px 50px;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            font-size: 15px;
            font-family: 'Poppins', sans-serif;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .input-wrapper input:focus {
            border-color: #10B981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #10B981 0%, #059669 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 600;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        .alert {
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: none;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        
        .alert-error {
            background: #FEE2E2;
            color: #DC2626;
            border: 1px solid #FECACA;
        }
        
        .alert-success {
            background: #D1FAE5;
            color: #059669;
            border: 1px solid #A7F3D0;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 30px;
            color: #9CA3AF;
            font-size: 13px;
        }
        
        @media (max-width: 768px) {
            .login-left {
                display: none;
            }
            .login-right {
                padding: 40px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <i class="fas fa-leaf leaf-icon"></i>
            <h1>Microgreens Admin</h1>
            <p>Manage your microgreens business with ease</p>
            <i class="fas fa-leaf leaf-decoration leaf-1"></i>
            <i class="fas fa-leaf leaf-decoration leaf-2"></i>
        </div>
        <div class="login-right">
            <h2>Welcome Back</h2>
            <p class="subtitle">Sign in to your admin account</p>
            
            <div id="alert" class="alert"></div>
            
            <form id="loginForm">
                <div class="form-group">
                    <label>Username or Email</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user"></i>
                        <input type="text" name="username" placeholder="Enter your username" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Password</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock"></i>
                        <input type="password" name="password" placeholder="Enter your password" required>
                    </div>
                </div>
                
                <button type="submit" class="btn-login" id="loginBtn">
                    <span>Sign In</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>
            
            <p class="footer-text">&copy; 2026 Microgreens Admin Panel</p>
        </div>
    </div>

    <script>
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const alertBox = document.getElementById('alert');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            
            alertBox.style.display = 'none';
            loginBtn.disabled = true;
            loginBtn.innerHTML = '<div class="spinner"></div><span>Signing in...</span>';
            
            const formData = new FormData(loginForm);
            formData.append('action', 'login');
            
            try {
                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = 'Login successful! Redirecting...';
                    alertBox.style.display = 'block';
                    
                    setTimeout(() => {
                        window.location.href = 'pages/dashboard.php';
                    }, 1000);
                } else {
                    alertBox.className = 'alert alert-error';
                    alertBox.textContent = result.message;
                    alertBox.style.display = 'block';
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = '<span>Sign In</span><i class="fas fa-arrow-right"></i>';
                }
            } catch (error) {
                alertBox.className = 'alert alert-error';
                alertBox.textContent = 'Connection error. Please try again.';
                alertBox.style.display = 'block';
                loginBtn.disabled = false;
                loginBtn.innerHTML = '<span>Sign In</span><i class="fas fa-arrow-right"></i>';
            }
        });
    </script>
</body>
</html>
