<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\User;
use Core\Controller;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        $this->redirectIfAuthenticated();

        $this->view('auth/login', [
            'title'        => 'Sign In',
            'flash'        => $this->getFlash(),
            'old'          => $_SESSION['old_input'] ?? [],
            'guestLayout'  => 'split',
        ], 'guest');

        unset($_SESSION['old_input']);
    }

    public function login(): void
    {
        $this->redirectIfAuthenticated();

        $email    = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $role     = (string) ($_POST['role'] ?? '');
        $remember = isset($_POST['remember']);

        $_SESSION['old_input'] = ['email' => $email, 'role' => $role];

        if ($email === '' || $password === '' || !in_array($role, ['customer', 'shopper'], true)) {
            $this->setFlash('error', 'Please fill in all fields and select a role.');
            $this->redirect('/login');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please enter a valid email address.');
            $this->redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);

        if (!$user || !$userModel->verifyPassword($password, (string) $user['password'])) {
            $this->setFlash('error', 'Invalid email or password.');
            $this->redirect('/login');
        }

        if ($user['role'] !== $role) {
            $roleLabel = $role === 'shopper' ? 'Personal Shopper' : 'Customer';
            $this->setFlash('error', "This account is not registered as a {$roleLabel}.");
            $this->redirect('/login');
        }

        unset($_SESSION['old_input']);
        $this->loginUser($user, $remember);
        $this->setFlash('success', 'Welcome back, ' . $user['name'] . '!');
        $this->redirectToRoleHome();
    }

    public function logout(): void
    {
        $this->logoutUser();
        session_start();
        $this->setFlash('success', 'You have been signed out.');
        $this->redirect('/login');
    }

    public function showRegister(): void
    {
        $this->redirectIfAuthenticated();

        $this->view('auth/register', [
            'title'       => 'Create Account',
            'flash'       => $this->getFlash(),
            'old'         => $_SESSION['old_input'] ?? [],
            'guestLayout' => 'split',
        ], 'guest');

        unset($_SESSION['old_input']);
    }

    public function register(): void
    {
        $this->redirectIfAuthenticated();

        $name            = trim((string) ($_POST['name'] ?? ''));
        $email           = trim((string) ($_POST['email'] ?? ''));
        $password        = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');
        $role            = (string) ($_POST['role'] ?? '');

        $_SESSION['old_input'] = [
            'name'  => $name,
            'email' => $email,
            'role'  => $role,
        ];

        if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
            $this->setFlash('error', 'All fields are required.');
            $this->redirect('/register');
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please enter a valid email address.');
            $this->redirect('/register');
        }

        if (!in_array($role, ['customer', 'shopper'], true)) {
            $this->setFlash('error', 'Please select a valid account type.');
            $this->redirect('/register');
        }

        if (strlen($password) < 8) {
            $this->setFlash('error', 'Password must be at least 8 characters.');
            $this->redirect('/register');
        }

        if ($password !== $confirmPassword) {
            $this->setFlash('error', 'Passwords do not match.');
            $this->redirect('/register');
        }

        $userModel = new User();

        if ($userModel->emailExists($email)) {
            $this->setFlash('error', 'An account with this email already exists.');
            $this->redirect('/register');
        }

        $userId = $userModel->create([
            'name'     => $name,
            'email'    => $email,
            'password' => $password,
            'role'     => $role,
        ]);

        unset($_SESSION['old_input']);

        $user = $userModel->findById($userId);

        if (!$user) {
            $this->setFlash('success', 'Account created. Please sign in.');
            $this->redirect('/login');
        }

        $this->loginUser($user);
        $this->setFlash('success', 'Welcome, ' . $name . '! Your account has been created.');
        $this->redirectToRoleHome();
    }
}
