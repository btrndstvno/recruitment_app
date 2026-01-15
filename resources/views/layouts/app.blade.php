<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Recruitment AdiPutro')</title>
    
    {{-- Vite Assets --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="has-sidebar">
    {{-- Sidebar --}}
    <aside class="sidebar" id="sidebar">
        {{-- Sidebar Header --}}
        <div class="sidebar-header">
            <div class="sidebar-brand" id="sidebarToggle" title="Toggle Sidebar">
                <span class="sidebar-brand-icon">
                    <i class="bi bi-briefcase-fill"></i>
                </span>
                <span class="sidebar-brand-text">Recruitment</span>
            </div>
        </div>

        {{-- Sidebar Menu --}}
        <nav class="sidebar-nav">
            <ul class="sidebar-menu">
                <li class="sidebar-menu-item">
                    <a href="{{ route('applicants.index') }}" class="sidebar-menu-link {{ request()->routeIs('applicants.*') && !request('archived') ? 'active' : '' }}" title="Dashboard">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-speedometer2"></i>
                        </span>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('applicants.index', ['archived' => '1']) }}" class="sidebar-menu-link {{ request('archived') == '1' ? 'active' : '' }}" title="Archive">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-archive"></i>
                        </span>
                        <span class="sidebar-menu-text">Archive</span>
                    </a>
                </li>
                <li class="sidebar-menu-item">
                    <a href="{{ route('import.form') }}" class="sidebar-menu-link {{ request()->routeIs('import.*') ? 'active' : '' }}" title="Import Excel">
                        <span class="sidebar-menu-icon">
                            <i class="bi bi-file-earmark-arrow-up"></i>
                        </span>
                        <span class="sidebar-menu-text">Import Excel</span>
                    </a>
                </li>
            </ul>
        </nav>

        {{-- Sidebar Footer - Profile --}}
        <div class="sidebar-footer">
            <div class="sidebar-profile" id="profileDropdownToggle">
                <div class="sidebar-profile-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                </div>
                <div class="sidebar-profile-info">
                    <span class="sidebar-profile-name">{{ Auth::user()->name }}</span>
                    <span class="sidebar-profile-role">HRD</span>
                </div>
                <i class="bi bi-chevron-up sidebar-profile-arrow"></i>
            </div>

            {{-- Profile Dropdown Menu --}}
            <div class="sidebar-profile-menu" id="profileDropdownMenu">
                <a href="{{ route('profile.show') }}" class="sidebar-profile-menu-item">
                    <i class="bi bi-person-circle"></i>
                    <span>Edit Profil</span>
                </a>
                <a href="{{ route('profile.show') }}#password" class="sidebar-profile-menu-item">
                    <i class="bi bi-key"></i>
                    <span>Ganti Password</span>
                </a>
                <hr class="my-2">
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="sidebar-profile-menu-item text-danger w-100">
                        <i class="bi bi-box-arrow-in-left"></i>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Main Content --}}
    <div class="main-wrapper" id="mainWrapper">
        {{-- Top Navbar (Mobile) --}}
        <nav class="topbar d-lg-none no-print">
            <button class="topbar-toggle" id="mobileSidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <span class="topbar-brand">Recruitment PT Adiputro</span>

            <div class="d-flex align-items-center gap-3 pe-3">
        
        <div class="topbar-profile d-lg-none">
            <span class="topbar-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
            </span>
        </div>
    </div>
        </nav>

        {{-- Page Content --}}
        <main class="main-content">
            @yield('content')
        </main>

        {{-- Footer --}}
        <footer class="main-footer no-print">
            <small>&copy; {{ date('Y') }} Recruitment App PT Adiputro. All rights reserved.</small>
        </footer>
    </div>

    {{-- Overlay for mobile --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- Sidebar Toggle Script --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const mainWrapper = document.getElementById('mainWrapper');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const profileToggle = document.getElementById('profileDropdownToggle');
            const profileMenu = document.getElementById('profileDropdownMenu');
            
            // Load saved state
            const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (isCollapsed) {
                sidebar.classList.add('collapsed');
                mainWrapper.classList.add('expanded');
            }
            
            // Desktop toggle
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                mainWrapper.classList.toggle('expanded');
                localStorage.setItem('sidebarCollapsed', sidebar.classList.contains('collapsed'));
                
                // Close profile menu when collapsing
                if (sidebar.classList.contains('collapsed')) {
                    profileMenu.classList.remove('show');
                }
            });
            
            // Mobile toggle
            if (mobileSidebarToggle) {
                mobileSidebarToggle.addEventListener('click', function() {
                    sidebar.classList.toggle('mobile-open');
                    sidebarOverlay.classList.toggle('show');
                });
            }
            
            // Overlay click
            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('show');
            });
            
            // Profile dropdown toggle
            profileToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                // If collapsed, expand sidebar first
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                    mainWrapper.classList.remove('expanded');
                    localStorage.setItem('sidebarCollapsed', 'false');
                    // Show menu after animation
                    setTimeout(() => {
                        profileMenu.classList.add('show');
                    }, 150);
                } else {
                    profileMenu.classList.toggle('show');
                }
            });
            
            // Click on menu items when collapsed - expand sidebar
            document.querySelectorAll('.sidebar-menu-link').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    if (sidebar.classList.contains('collapsed')) {
                        e.preventDefault();
                        sidebar.classList.remove('collapsed');
                        mainWrapper.classList.remove('expanded');
                        localStorage.setItem('sidebarCollapsed', 'false');
                        // Navigate after animation
                        const href = this.getAttribute('href');
                        setTimeout(() => {
                            window.location.href = href;
                        }, 200);
                    }
                });
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!profileToggle.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.remove('show');
                }
            });
        });
    </script>

    {{-- SweetAlert untuk session messages --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: @json(session('success')),
                showConfirmButton: true,
                confirmButtonColor: '#0d6efd',
                timer: 4000,
                timerProgressBar: true
            });
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: @json(session('error')),
                showConfirmButton: true,
                confirmButtonColor: '#dc3545'
            });
        });
    </script>
    @endif

    @if(session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'info',
                title: 'Informasi',
                text: @json(session('info')),
                showConfirmButton: true,
                confirmButtonColor: '#0dcaf0'
            });
        });
    </script>
    @endif

    @if(session('warning'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'warning',
                title: 'Warning',
                text: @json(session('warning')),
                showConfirmButton: true,
                confirmButtonColor: '#ffc107'
            });
        });
    </script>
    @endif

    {{-- Validation Errors di resources/views/layouts/app.blade.php --}}
    @if($errors->any() && !session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let errorMessage = "";

            @if($errors->has('password'))
                errorMessage = "Password atau konfirmasi password Anda tidak valid.";
            @else
                // Jika bukan Password, tampilkan error pertama dari Laravel
                errorMessage = @json($errors->first());
            @endif

            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                text: errorMessage,
                showConfirmButton: true,
                confirmButtonColor: '#dc3545'
            });
        });
    </script>
    @endif

    {{-- Loading untuk form submission --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add loading to all forms with data-loading attribute
            document.querySelectorAll('form[data-loading="true"]').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    // Check if form is valid
                    if (!form.checkValidity()) {
                        return;
                    }
                    
                    Swal.fire({
                        title: 'Mohon Tunggu...',
                        text: 'Sedang memproses data',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                });
            });

            // Confirm delete
            document.querySelectorAll('form[data-confirm="true"]').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah Anda yakin?',
                        text: "Data yang dihapus tidak dapat dikembalikan!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#dc3545',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.fire({
                                title: 'Menghapus...',
                                allowOutsideClick: false,
                                allowEscapeKey: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
