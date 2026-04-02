<div class="w-full bg-surface-light dark:bg-surface-dark border-b border-border-light dark:border-border-dark sticky top-0 z-50">
    <div class="max-w-[1200px] mx-auto px-4 sm:px-6 lg:px-8">
        <header class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <div class="flex items-center gap-3">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-primary text-white">
                    <span class="material-symbols-outlined text-xl">analytics</span>
                </div>
                <a href="{{ route('dashboard') }}">
                    <h2 class="text-text-primary-light dark:text-text-primary-dark text-xl font-bold tracking-tight">SIMOPANG</h2>
                </a>
            </div>

            {{-- Desktop Nav --}}
            <nav class="hidden md:flex items-center gap-8">
                @php
                    $navLinks = [
                        ['route' => 'dashboard',       'label' => 'Dashboard'],
                        ['route' => 'data-harga.index','label' => 'Data Harga'],
                        ['route' => 'prediksi.index',  'label' => 'Prediksi'],
                        ['route' => 'simulasi.index',  'label' => 'Simulasi'],
                        ['route' => 'tentang',         'label' => 'Tentang'],
                    ];
                @endphp

                @foreach($navLinks as $link)
                    <a href="{{ route($link['route']) }}"
                       class="{{ request()->routeIs(explode('.', $link['route'])[0] . '*') ? 'text-primary font-semibold' : 'text-text-secondary-light dark:text-text-secondary-dark font-medium' }} text-sm hover:text-primary dark:hover:text-blue-400 transition-colors">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>

            {{-- Right Actions --}}
            <div class="flex items-center gap-2">
                <button onclick="toggleDarkMode()"
                        class="p-2 text-text-secondary-light dark:text-text-secondary-dark hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors">
                    <span class="material-symbols-outlined" id="dark-mode-icon">dark_mode</span>
                </button>

                <a href="{{ url('/admin/dashboard') }}"
                   class="hidden md:flex items-center gap-1.5 text-sm font-medium text-text-secondary-light dark:text-text-secondary-dark border border-border-light dark:border-border-dark px-3 py-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    Admin Login
                </a>

                <button onclick="toggleMobileMenu()"
                        class="md:hidden p-2 text-text-secondary-light dark:text-text-secondary-dark hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg">
                    <span class="material-symbols-outlined">menu</span>
                </button>
            </div>
        </header>
    </div>

    {{-- Mobile Menu --}}
    <div id="mobile-menu" class="hidden md:hidden border-t border-border-light dark:border-border-dark bg-surface-light dark:bg-surface-dark">
        <nav class="max-w-[1200px] mx-auto px-4 py-3 flex flex-col gap-1">
            @foreach($navLinks as $link)
                <a href="{{ route($link['route']) }}"
                   class="{{ request()->routeIs(explode('.', $link['route'])[0] . '*') ? 'bg-primary-light text-primary' : 'text-text-secondary-light dark:text-text-secondary-dark' }} px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-primary-light hover:text-primary transition-colors">
                    {{ $link['label'] }}
                </a>
            @endforeach

            <a href="{{ url('/admin/dashboard') }}"
               class="text-text-secondary-light dark:text-text-secondary-dark px-4 py-2.5 rounded-lg text-sm font-medium hover:bg-primary-light hover:text-primary transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">lock</span>
                Admin Login
            </a>
        </nav>
    </div>
</div>