<?php
session_start();
require_once "../config/api.php"; // fungsi callAPI()
$error = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);
    if ($email && $password) {
        $response = callAPI("POST", "https://mokkoproject.biz.id/Mokko_Businness/src/api/login.php", [
            "Email"    => $email,
            "Password" => $password
        ]);
        if (isset($response['token'])) {
            // Simpan JWT & user info di session
            $_SESSION['jwt']  = $response['token'];
            $_SESSION['role'] = $response['role'] ?? "customer"; 
            $_SESSION['name'] = $response['name'] ?? $email;
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $response['error'] ?? "Login gagal";
        }
    } else {
        $error = "Email dan Password harus diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mokko Project</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="https://www.mokko.co.id/images/fevicon/icon.png" type="image/gif">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'jordy-blue': {
                            DEFAULT: '#8BB4E4',
                            100: '#0e233b',
                            200: '#1c4677',
                            300: '#2a6ab2',
                            400: '#508ed6',
                            500: '#8bb4e4',
                            600: '#a2c4e9',
                            700: '#bad2ef',
                            800: '#d1e1f4',
                            900: '#e8f0fa'
                        },
                        'un-blue': {
                            DEFAULT: '#5592D6',
                            100: '#0c1d30',
                            200: '#173a61',
                            300: '#235791',
                            400: '#2f73c2',
                            500: '#5592d6',
                            600: '#78a8de',
                            700: '#9abee7',
                            800: '#bcd3ef',
                            900: '#dde9f7'
                        },
                        'un-blue-2': {
                            DEFAULT: '#5B91D1',
                            100: '#0d1c2f',
                            200: '#1a395e',
                            300: '#28558d',
                            400: '#3572bc',
                            500: '#5b91d1',
                            600: '#7da7da',
                            700: '#9dbde3',
                            800: '#bed3ed',
                            900: '#dee9f6'
                        },
                        'bronze': {
                            DEFAULT: '#DA8235',
                            100: '#2e1a08',
                            200: '#5b3411',
                            300: '#894d19',
                            400: '#b76722',
                            500: '#da8235',
                            600: '#e19b5d',
                            700: '#e9b485',
                            800: '#f0cdae',
                            900: '#f8e6d6'
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.6s ease-out',
                        'slide-up': 'slideUp 0.8s ease-out',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' },
                        },
                        slideUp: {
                            '0%': { 
                                opacity: '0',
                                transform: 'translateY(20px)'
                            },
                            '100%': { 
                                opacity: '1',
                                transform: 'translateY(0)'
                            }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .minimal-bg {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        }
        
        .form-container {
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .input-field {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .input-field:focus {
            border-color: #5B91D1;
            box-shadow: 0 0 0 3px rgba(91, 145, 209, 0.1);
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: #5B91D1;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary:hover {
            background: #5592D6;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(91, 145, 209, 0.2);
        }
        
        .btn-primary:active {
            transform: translateY(0);
        }
        
        .logo-accent {
            color: #5B91D1;
            transition: all 0.3s ease;
        }
        
        .logo-accent:hover {
            color: #5592D6;
        }
        
        .error-box {
            animation: slideIn 0.3s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-10px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        .form-label {
            color: #6c757d;
            font-weight: 500;
            font-size: 0.875rem;
            letter-spacing: 0.025em;
        }
        
        .link-primary {
            color: #5B91D1;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .link-primary:hover {
            color: #5592D6;
        }
        
        .divider {
            background: linear-gradient(to right, transparent, #e9ecef, transparent);
        }
        
        .subtle-shadow {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.01);
        }
        
        /* Loading spinner */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 0.8s ease-in-out infinite;
        }
        
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        
        /* Focus states */
        .input-wrapper:focus-within .input-icon {
            color: #5B91D1;
        }
        
        /* Background pattern */
        .bg-pattern {
            background-image: 
                radial-gradient(circle at 25% 25%, rgba(139, 180, 228, 0.05) 0%, transparent 50%),
                radial-gradient(circle at 75% 75%, rgba(91, 145, 209, 0.05) 0%, transparent 50%);
        }
    </style>
</head>
<body class="minimal-bg min-h-screen flex items-center justify-center p-4 bg-pattern">
    <!-- Main Container -->
    <div class="w-full max-w-sm" data-aos="fade-up">
        <!-- Form Card -->
        <div class="form-container rounded-2xl p-8 subtle-shadow">
            <!-- Logo Section -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center justify-center w-12 h-12 mb-4">
                    <i class="text-3xl logo-accent fas fa-user-circle"></i>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">Masuk</h1>
                <p class="text-sm text-gray-600 mt-1">Ke akun Mokko Project Anda</p>
            </div>
            
            <!-- Error Message -->
            <?php if ($error): ?>
                <div class="mb-6 p-3 bg-red-50 border border-red-200 text-red-700 rounded-lg text-sm error-box">
                    <div class="flex items-center">
                        <i class="mr-2 text-red-500 fas fa-exclamation-circle"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                </div>
            <?php endif; ?>
            
            <!-- Login Form -->
            <form method="POST" id="loginForm" class="space-y-5">
                <!-- Email Field -->
                <div class="input-wrapper">
                    <label for="email" class="block form-label mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-envelope input-icon"></i>
                        </div>
                        <input type="email" 
                               name="email" 
                               id="email" 
                               required
                               class="input-field w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none"
                               placeholder="nama@email.com">
                    </div>
                </div>
                
                <!-- Password Field -->
                <div class="input-wrapper">
                    <label for="password" class="block form-label mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <i class="text-gray-400 fas fa-lock input-icon"></i>
                        </div>
                        <input type="password" 
                               name="password" 
                               id="password" 
                               required
                               class="input-field w-full pl-10 pr-3 py-3 border border-gray-200 rounded-lg text-gray-900 placeholder-gray-400 focus:outline-none"
                               placeholder="••••••••">
                    </div>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" 
                        id="submitBtn"
                        class="btn-primary w-full py-3 px-4 text-white font-medium rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-un-blue-2">
                    <span id="btnText">Masuk</span>
                    <span id="btnLoader" class="hidden">
                        <span class="spinner"></span>
                    </span>
                </button>
            </form>
            
            <!-- Divider -->
            <div class="my-6">
                <div class="divider h-px w-full"></div>
            </div>
            
            <!-- Register Link -->
            <div class="text-center">
                <p class="text-sm text-gray-600">
                    Belum punya akun? 
                    <a href="register.php" class="link-primary hover:underline">
                        Daftar
                    </a>
                </p>
            </div>
            
            <!-- Brand Footer -->
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <div class="flex items-center justify-center space-x-2 mb-2">
                    <img src="https://www.mokko.co.id/images/logos/mokkologo.png" alt="Mokko Project" class="h-5">
                    <span class="text-xs text-gray-500 font-medium">Mokko Project</span>
                </div>
                <p class="text-xs text-gray-400">© <?= date("Y") ?> All rights reserved.</p>
            </div>
        </div>
    </div>
    
    <script>
        // Initialize AOS
        AOS.init({
            duration: 600,
            once: true,
            offset: 50
        });
        
        // Form submission handling
        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnLoader = document.getElementById('btnLoader');
        
        loginForm.addEventListener('submit', function(e) {
            // Show loading state
            btnText.style.display = 'none';
            btnLoader.style.display = 'inline-block';
            submitBtn.disabled = true;
            
            // Prevent multiple submissions
            submitBtn.style.cursor = 'not-allowed';
        });
        
        // Add subtle animations to inputs
        const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.parentElement.classList.add('scale-[1.02]');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.parentElement.classList.remove('scale-[1.02]');
            });
        });
        
        // Smooth page transitions
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.3s ease-in-out';
                document.body.style.opacity = '1';
            }, 100);
        });
        
        // Add keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
                const form = e.target.closest('form');
                if (form) {
                    form.dispatchEvent(new Event('submit'));
                }
            }
        });
    </script>
</body>
</html>