<?php
declare(strict_types=1);

class AuthController
{
    public function loginForm(): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }
        view('auth/login', [], false);
    }

    public function login(): void
    {
        csrf_check();
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            flash_set('danger', 'Please enter your username and password.');
            redirect('login');
        }

        if (Auth::attempt($username, $password)) {
            $u = Auth::user();
            flash_set('success', 'Welcome back, ' . ($u['full_name'] ?: $u['username']) . '!');
            redirect('dashboard');
        }

        flash_set('danger', 'Invalid username or password.');
        redirect('login');
    }

    public function logout(): void
    {
        Auth::logout();
        flash_set('success', 'You have been signed out.');
        redirect('login');
    }
}
