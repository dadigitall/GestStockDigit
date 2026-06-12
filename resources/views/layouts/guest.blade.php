<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'StockFlow 360°') }} — Connexion</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    @vite(['resources/js/app.js'])
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Figtree', sans-serif; background: #020618; color: #fff; min-height: 100vh; overflow-y: auto; }
        
        /* Animated background blobs */
        .bg-blob { position: fixed; border-radius: 50%; filter: blur(120px); opacity: 0.3; pointer-events: none; z-index: 0; }
        .bg-blob-1 { width: 500px; height: 500px; background: #7c3aed; top: -150px; right: -100px; animation: blobFloat 20s ease-in-out infinite; }
        .bg-blob-2 { width: 400px; height: 400px; background: #2563eb; bottom: -100px; left: -80px; animation: blobFloat 25s ease-in-out infinite reverse; }
        .bg-blob-3 { width: 300px; height: 300px; background: #06b6d4; top: 50%; left: 50%; transform: translate(-50%, -50%); animation: blobFloat 18s ease-in-out infinite 5s; }
        
        @keyframes blobFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(40px, -60px) scale(1.1); }
            50% { transform: translate(-30px, 40px) scale(0.9); }
            75% { transform: translate(50px, 30px) scale(1.05); }
        }

        /* Grid overlay */
        .grid-overlay { position: fixed; inset: 0; opacity: 0.03; z-index: 0; pointer-events: none; background-image: linear-gradient(rgba(255,255,255,0.8) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.8) 1px, transparent 1px); background-size: 60px 60px; }

        /* Login card */
        .login-container { position: relative; z-index: 10; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 1.5rem; }
        .login-card { width: 100%; max-width: 420px; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(24px); -webkit-backdrop-filter: blur(24px); border: 1px solid rgba(255,255,255,0.08); border-radius: 1.25rem; padding: 2.5rem; box-shadow: 0 25px 60px rgba(0,0,0,0.5); animation: cardAppear 0.8s ease-out; }
        
        @keyframes cardAppear {
            0% { opacity: 0; transform: translateY(20px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Brand */
        .brand { display: flex; align-items: center; gap: 0.75rem; justify-content: center; margin-bottom: 2rem; text-decoration: none; }
        .brand-icon { width: 2.75rem; height: 2.75rem; border-radius: 0.75rem; background: linear-gradient(135deg, #7c3aed, #4f46e5, #2563eb); display: flex; align-items: center; justify-content: center; box-shadow: 0 8px 24px rgba(99, 102, 241, 0.3); transition: transform 0.3s ease; }
        .brand-icon:hover { transform: scale(1.05); }
        .brand-icon svg { width: 1.25rem; height: 1.25rem; color: #fff; }
        .brand-text { display: flex; flex-direction: column; line-height: 1.2; }
        .brand-name { font-size: 1.25rem; font-weight: 800; color: #fff; }
        .brand-name span { color: #818cf8; }
        .brand-sub { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.15em; color: #64748b; }

        /* Form elements */
        .form-title { text-align: center; margin-bottom: 1.75rem; }
        .form-title h2 { font-size: 1.5rem; font-weight: 700; color: #f1f5f9; }
        .form-title p { font-size: 0.875rem; color: #64748b; margin-top: 0.375rem; }

        .input-group { margin-bottom: 1.25rem; }
        .input-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #cbd5e1; margin-bottom: 0.4rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .input-wrapper { position: relative; }
        .input-wrapper .input-icon { position: absolute; left: 0.875rem; top: 50%; transform: translateY(-50%); width: 1.125rem; height: 1.125rem; color: #475569; pointer-events: none; transition: color 0.3s ease; }
        .input-wrapper:focus-within .input-icon { color: #818cf8; }
        .input-wrapper input { width: 100%; padding: 0.75rem 1rem 0.75rem 2.625rem; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(71, 85, 105, 0.4); border-radius: 0.75rem; color: #f1f5f9; font-size: 0.9rem; outline: none; transition: all 0.3s ease; }
        .input-wrapper input:focus { border-color: #6366f1; background: rgba(30, 41, 59, 0.8); box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15); }
        .input-wrapper input::placeholder { color: #475569; }
        .input-wrapper input.error { border-color: #ef4444; }
        .input-wrapper input.error:focus { box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.15); }

        .error-msg { font-size: 0.75rem; color: #f87171; margin-top: 0.375rem; display: flex; align-items: center; gap: 0.25rem; }

        .checkbox-group { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .checkbox-label { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
        .checkbox-label input[type="checkbox"] { width: 1rem; height: 1rem; border-radius: 0.25rem; background: rgba(30, 41, 59, 0.6); border: 1px solid rgba(71, 85, 105, 0.4); accent-color: #6366f1; cursor: pointer; }
        .checkbox-label span { font-size: 0.85rem; color: #94a3b8; user-select: none; }

        .forgot-link { font-size: 0.85rem; color: #818cf8; text-decoration: none; transition: color 0.2s ease; }
        .forgot-link:hover { color: #a5b4fc; text-decoration: underline; }

        .login-btn { width: 100%; padding: 0.8rem 1.5rem; background: linear-gradient(135deg, #7c3aed, #4f46e5); border: none; border-radius: 0.75rem; color: #fff; font-size: 0.95rem; font-weight: 700; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 16px rgba(99, 102, 241, 0.3); position: relative; overflow: hidden; }
        .login-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(99, 102, 241, 0.4); }
        .login-btn:active { transform: translateY(0); }
        .login-btn:disabled { opacity: 0.7; cursor: not-allowed; transform: none; }
        .login-btn .btn-content { display: flex; align-items: center; justify-content: center; gap: 0.5rem; }
        .login-btn .spinner { display: none; width: 1.125rem; height: 1.125rem; border: 2px solid rgba(255,255,255,0.3); border-top-color: #fff; border-radius: 50%; animation: spin 0.6s linear infinite; }
        .login-btn.loading .btn-text { opacity: 0.7; }
        .login-btn.loading .spinner { display: block; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .back-link { display: flex; align-items: center; justify-content: center; gap: 0.375rem; margin-top: 1.5rem; font-size: 0.85rem; color: #64748b; text-decoration: none; transition: color 0.2s ease; }
        .back-link:hover { color: #94a3b8; }
        .back-link svg { width: 1rem; height: 1rem; }

        /* Session status */
        .session-status { padding: 0.75rem 1rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); border-radius: 0.75rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #6ee7b7; text-align: center; animation: fadeIn 0.5s ease; }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(-5px); } to { opacity: 1; transform: translateY(0); } }

        /* Mobile responsive */
        @media (max-width: 480px) {
            .login-card { padding: 1.75rem; border-radius: 1rem; }
            .brand-icon { width: 2.25rem; height: 2.25rem; }
            .brand-name { font-size: 1.1rem; }
        }
    </style>
</head>
<body>
    <!-- Background blobs -->
    <div class="bg-blob bg-blob-1"></div>
    <div class="bg-blob bg-blob-2"></div>
    <div class="bg-blob bg-blob-3"></div>
    <div class="grid-overlay"></div>

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card">
            <!-- Brand -->
            <a href="/" class="brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                        <path d="M3 7l9-4 9 4-9 4-9-4z"></path>
                        <path d="M3 12l9 4 9-4"></path>
                        <path d="M3 17l9 4 9-4"></path>
                    </svg>
                </div>
                <div class="brand-text">
                    <span class="brand-name">StockFlow <span>360°</span></span>
                    <span class="brand-sub">Gestion commerciale</span>
                </div>
            </a>

            <!-- Form Title -->
            <div class="form-title">
                <h2>Connexion</h2>
                <p>Accédez à votre espace de gestion</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="session-status">
                    {{ session('status') }}
                </div>
            @endif

            {{ $slot }}
        </div>
    </div>

    <script>
        function initLoginPage() {
            const form = document.querySelector('form[wire\\:submit="login"]');
            const btn = form?.querySelector('button[type="submit"]');
            
            if (form && btn) {
                form.addEventListener('submit', function() {
                    btn.classList.add('loading');
                    btn.disabled = true;
                });
            }

            const emailInput = document.getElementById('email');
            if (emailInput) {
                setTimeout(() => emailInput.focus(), 300);
            }

            const formElements = document.querySelectorAll('.input-group, .checkbox-group, .login-btn, .back-link, .form-title');
            formElements.forEach((el, i) => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(10px)';
                setTimeout(() => {
                    el.style.transition = 'all 0.5s ease';
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }, 200 + (i * 100));
            });
        }

        document.addEventListener('DOMContentLoaded', initLoginPage);
        document.addEventListener('livewire:navigated', initLoginPage);

        // Re-enable button on Livewire update (fix for stuck loading state)
        document.addEventListener('livewire:update', function() {
            const btn = document.querySelector('button[type="submit"].loading');
            if (btn) {
                btn.classList.remove('loading');
                btn.disabled = false;
            }
        });
    </script>
</body>
</html>
