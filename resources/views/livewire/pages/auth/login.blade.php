<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component
{
    public LoginForm $form;

    /**
     * Handle an incoming authentication request.
     */
    public function login(): void
    {
        $this->validate();

        $this->form->authenticate();

        Session::regenerate();

        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div>
    <form wire:submit="login">
        <!-- Email -->
        <div class="input-group">
            <label for="email">Adresse email</label>
            <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <input wire:model="form.email" id="email" type="email" name="email" required autofocus autocomplete="username" placeholder="exemple@email.com" class="{{ $errors->has('form.email') ? 'error' : '' }}">
            </div>
            @if ($errors->has('form.email'))
                <div class="error-msg">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('form.email') }}
                </div>
            @endif
        </div>

        <!-- Password -->
        <div class="input-group">
            <label for="password">Mot de passe</label>
            <div class="input-wrapper">
                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                <input wire:model="form.password" id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" class="{{ $errors->has('form.password') ? 'error' : '' }}">
            </div>
            @if ($errors->has('form.password'))
                <div class="error-msg">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    {{ $errors->first('form.password') }}
                </div>
            @endif
        </div>

        <!-- Remember & Forgot -->
        <div class="checkbox-group">
            <label class="checkbox-label">
                <input wire:model="form.remember" type="checkbox" name="remember">
                <span>Se souvenir de moi</span>
            </label>
            @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="forgot-link" wire:navigate>
                    Mot de passe oublié ?
                </a>
            @endif
        </div>

        <!-- Submit -->
        <button type="submit" class="login-btn">
            <span class="btn-content">
                <span class="btn-text">Se connecter</span>
                <div class="spinner"></div>
            </span>
        </button>

        <!-- Back to home -->
        <a href="/" class="back-link">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Retour à l'accueil
        </a>
    </form>
</div>
