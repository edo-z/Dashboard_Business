<?php
session_start();
// kalau sudah login → langsung dashboard
if (!empty($_SESSION['jwt'])) {
    header("Location: pages/dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mokko Project - Business Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="https://www.mokko.co.id/images/fevicon/icon.png" type="image/gif">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@2.3.1/dist/aos.css" />
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                        'float': 'float 6s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-20px)' },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #5B91D1 0%, #5592D6 50%, #8BB4E4 100%);
        }
        
        .glass-effect {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        
        .hero-pattern {
            background-image: 
                radial-gradient(circle at 20% 80%, rgba(139, 180, 228, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(91, 145, 209, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(85, 146, 214, 0.2) 0%, transparent 50%);
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .card-hover:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #DA8235 0%, #e19b5d 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(218, 130, 53, 0.3);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            border: 2px solid #5B91D1;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background: #5B91D1;
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(91, 145, 209, 0.3);
        }
        
        .stat-card {
            background: linear-gradient(135deg, rgba(255,255,255,0.9) 0%, rgba(255,255,255,0.7) 100%);
            backdrop-filter: blur(10px);
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .floating {
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-delay-1 {
            animation: float 6s ease-in-out infinite;
            animation-delay: 1s;
        }
        
        .floating-delay-2 {
            animation: float 6s ease-in-out infinite;
            animation-delay: 2s;
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #5B91D1 0%, #5592D6 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .parallax-element {
            transition: transform 0.5s ease-out;
        }
        
        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 1rem;
            }
        }
    </style>
</head>
<body class="gradient-bg min-h-screen overflow-x-hidden">
    <!-- Navigation -->
    <nav class="fixed top-0 w-full z-50 glass-effect border-b border-white/20">
    <div class="container mx-auto px-6 py-4">
        <div class="flex items-center justify-center">
            <img src="https://www.mokko.co.id/images/logos/mokkologo.png" alt="Mokko Project" class="h-10">
        </div>
    </div>
</nav>

    <!-- Hero Section -->
    <section class="hero-section min-h-screen flex items-center justify-center relative hero-pattern">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 left-10 w-72 h-72 bg-jordy-blue/20 rounded-full blur-3xl floating"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-un-blue/20 rounded-full blur-3xl floating-delay-1"></div>
            <div class="absolute top-1/2 left-1/2 w-80 h-80 bg-bronze/10 rounded-full blur-3xl floating-delay-2"></div>
        </div>

        <div class="container mx-auto px-6 z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Hero Content -->
                <div data-aos="fade-right" data-aos-duration="1000">
                    <h1 class="text-5xl lg:text-6xl font-bold text-white mb-6 leading-tight">
                        Sistem Manajemen Bisnis
                    </h1>
                    <p class="text-xl text-white/90 mb-8 leading-relaxed">
                        Kelola formulir bisnis Anda dengan mudah melalui sistem terintegrasi untuk Quotation, Invoice, dan Delivery Order.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 mb-12">
                        <a href="pages/login.php" 
                           class="px-8 py-4 font-semibold text-white btn-primary rounded-xl shadow-lg text-center">
                            <i class="mr-2 fas fa-rocket"></i> Mulai Sekarang
                        </a>
                        <a href="pages/register.php" 
                           class="px-8 py-4 font-semibold text-un-blue-2 btn-secondary rounded-xl shadow-lg text-center">
                            <i class="mr-2 fas fa-user-plus"></i> Daftar Admin
                        </a>
                    </div>
                </div>

                <!-- Hero Visual -->
                <div data-aos="fade-left" data-aos-duration="1000" class="relative">
                    <div class="glass-effect rounded-2xl p-8 shadow-2xl">
                        <div class="bg-white rounded-xl p-6 mb-4 card-hover">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-un-blue-2/10 rounded-full flex items-center justify-center">
                                        <i class="text-un-blue-2 fas fa-file-invoice"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold">Quotation #QTN-XI/25/0001</div>
                                        <div class="text-sm text-gray-500">PT. Maju Bersama Mokko</div>
                                    </div>
                                </div>
                                <div class="text-un-blue-2 font-bold">Rp 15.000.000</div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl p-6 mb-4 card-hover">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-bronze/10 rounded-full flex items-center justify-center">
                                        <i class="text-bronze fas fa-receipt"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold">Invoice #INV-XI/25/0001</div>
                                        <div class="text-sm text-gray-500">Dibayar</div>
                                    </div>
                                </div>
                                <div class="text-bronze font-bold">Rp 15.000.000</div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl p-6 card-hover">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-jordy-blue/10 rounded-full flex items-center justify-center">
                                        <i class="text-jordy-blue fas fa-truck"></i>
                                    </div>
                                    <div>
                                        <div class="font-semibold">DO #DO-XI/25/001</div>
                                        <div class="text-sm text-gray-500">Dalam Pengiriman</div>
                                    </div>
                                </div>
                                <div class="text-jordy-blue font-bold">5 Item</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true,
            offset: 100
        });

        // Parallax effect
        document.addEventListener('mousemove', (e) => {
            const elements = document.querySelectorAll('.parallax-element');
            const x = e.clientX / window.innerWidth;
            const y = e.clientY / window.innerHeight;
            
            elements.forEach(element => {
                const speed = element.dataset.speed || 1;
                const xPos = (x - 0.5) * speed * 20;
                const yPos = (y - 0.5) * speed * 20;
                element.style.transform = `translate(${xPos}px, ${yPos}px)`;
            });
        });

        // Smooth scrolling for navigation links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Add scroll effect to navigation
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('shadow-lg');
            } else {
                nav.classList.remove('shadow-lg');
            }
        });
    </script>
</body>
</html>