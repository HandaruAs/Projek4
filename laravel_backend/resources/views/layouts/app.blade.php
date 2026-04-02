<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="light">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SIMOPANG') - Sistem Monitoring Pangan</title>

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary":              "#137fec",
                        "primary-dark":         "#0b5db0",
                        "primary-light":        "#e0f0ff",
                        "background-light":     "#f6f7f8",
                        "background-dark":      "#101922",
                        "surface-light":        "#ffffff",
                        "surface-dark":         "#1a2634",
                        "text-primary-light":   "#0d141b",
                        "text-primary-dark":    "#e2e8f0",
                        "text-secondary-light": "#4c739a",
                        "text-secondary-dark":  "#94a3b8",
                        "border-light":         "#e7edf3",
                        "border-dark":          "#2d3748",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                    borderRadius: {
                        "DEFAULT": "0.5rem", "lg": "0.75rem",
                        "xl": "1rem", "full": "9999px"
                    },
                },
            },
        }
    </script>
{{-- Alpine.js untuk animasi --}}
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<style>
    /* PAGE TRANSITION */
    .page-enter {
        animation: pageEnter 0.4s ease forwards;
    }

    @keyframes pageEnter {
        from {
            opacity: 0;
            transform: translateY(16px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* FADE IN UP untuk cards */
    .fade-in-up {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Delay classes */
    .delay-100 { animation-delay: 0.1s; }
    .delay-200 { animation-delay: 0.2s; }
    .delay-300 { animation-delay: 0.3s; }
    .delay-400 { animation-delay: 0.4s; }
    .delay-500 { animation-delay: 0.5s; }

    /* NAVBAR LINK hover underline */
    nav a {
        position: relative;
    }
    nav a::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 0;
        height: 2px;
        background: #137fec;
        transition: width 0.25s ease;
    }
    nav a:hover::after {
        width: 100%;
    }

    /* CARD HOVER */
    .card-hover {
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }
    .card-hover:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(19, 127, 236, 0.1);
    }

    /* BUTTON PRESS */
    button, a {
        transition: all 0.2s ease;
    }
    button:active {
        transform: scale(0.97);
    }

    /* SKELETON PULSE */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: skeleton 1.5s infinite;
    }
    @keyframes skeleton {
        0%   { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* SMOOTH SCROLL */
    html {
        scroll-behavior: smooth;
    }

    /* PAGE LEAVE */
    .page-leave {
        animation: pageLeave 0.25s ease forwards;
    }
    @keyframes pageLeave {
        to {
            opacity: 0;
            transform: translateY(-8px);
        }
    }
</style>
    @stack('styles')
</head>
<body class="bg-background-light dark:bg-background-dark font-display min-h-screen flex flex-col antialiased transition-colors duration-200">

    @include('layouts.navbar')

<main class="flex-grow page-enter">
    @yield('content')
</main>

    @include('layouts.footer')

    {{-- Toast Notification --}}
    <div id="toast" class="fixed bottom-6 right-6 z-50 hidden items-center gap-3 px-5 py-3 rounded-xl shadow-lg border text-sm font-medium transition-all duration-300">
        <span class="material-symbols-outlined text-[18px]" id="toast-icon">check_circle</span>
        <span id="toast-message">Berhasil!</span>
    </div>
    <script>
    // PAGE TRANSITION saat klik link
    document.addEventListener('DOMContentLoaded', () => {
        const main = document.querySelector('main');

        document.querySelectorAll('a[href]').forEach(link => {
            // Skip link eksternal, anchor, dan javascript
            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('http') || href.startsWith('javascript')) return;

            link.addEventListener('click', function (e) {
                e.preventDefault();
                const target = this.href;

                main.classList.remove('page-enter');
                main.classList.add('page-leave');

                setTimeout(() => {
                    window.location.href = target;
                }, 220);
            });
        });
    });

    // INTERSECTION OBSERVER untuk fade-in-up otomatis
    document.addEventListener('DOMContentLoaded', () => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.observe-fade').forEach(el => observer.observe(el));
    });
</script>

    @stack('scripts')

    <script>
        // DARK MODE
        const htmlEl = document.documentElement;

        function toggleDarkMode() {
            htmlEl.classList.toggle('dark');
            localStorage.setItem('theme', htmlEl.classList.contains('dark') ? 'dark' : 'light');
            updateDarkModeIcon();
        }

        function updateDarkModeIcon() {
            const icon = document.getElementById('dark-mode-icon');
            if (icon) icon.textContent = htmlEl.classList.contains('dark') ? 'light_mode' : 'dark_mode';
        }

        if (localStorage.getItem('theme') === 'dark') htmlEl.classList.add('dark');
        document.addEventListener('DOMContentLoaded', updateDarkModeIcon);

        // MOBILE MENU
        function toggleMobileMenu() {
            document.getElementById('mobile-menu').classList.toggle('hidden');
        }

        // TOAST
        function showToast(message, type = 'success') {
            const toast = document.getElementById('toast');
            const styles = {
                success: 'bg-green-50 border-green-200 text-green-800',
                error:   'bg-red-50 border-red-200 text-red-800',
                info:    'bg-blue-50 border-blue-200 text-blue-800',
                warning: 'bg-amber-50 border-amber-200 text-amber-800',
            };
            const icons = { success: 'check_circle', error: 'error', info: 'info', warning: 'warning' };

            toast.className = `fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-3 rounded-xl shadow-lg border text-sm font-medium transition-all duration-300 ${styles[type]}`;
            document.getElementById('toast-icon').textContent    = icons[type];
            document.getElementById('toast-message').textContent = message;

            toast.classList.remove('hidden');
            setTimeout(() => toast.classList.add('hidden'), 3500);
        }

        // CSRF untuk AJAX
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    </script>
</body>
</html>