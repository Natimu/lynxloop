<?php

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin(): void
    {
        Auth::requireGuest();

        $this->view('auth/login', [
            'title' => 'Login',
        ]);
    }

    public function showRegister(): void
    {
        Auth::requireGuest();

        $this->view('auth/register', [
            'title' => 'Register',
        ]);
    }

    public function registerForm(): void
    {
        if (isset($_SESSION['user_id'])) {
            
            $this->redirect('/register');
        }

        $this->view('auth/register', [
            'errors' => [],
            'old' => []
        ]);
    }

    public function register(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $universityRole = trim($_POST['university_role'] ?? 'student');

        $errors = [];
        $old = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'university_role' => $universityRole
        ];

        if ($firstName === '') {
            $errors['first_name'] = 'First name is required.';
        }

        if ($lastName === '') {
            $errors['last_name'] = 'Last name is required.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Enter a valid email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if ($confirmPassword === '') {
            $errors['confirm_password'] = 'Please confirm your password.';
        } elseif ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        $allowedRoles = ['student', 'alumni', 'faculty'];
        if (!in_array($universityRole, $allowedRoles, true)) {
            $errors['university_role'] = 'Invalid role selected.';
        }

        $userModel = new User();

        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $existingUser = $userModel->findByEmail($email);
            if ($existingUser) {
                $errors['email'] = 'That email is already registered.';
            }
        }

        if (!empty($errors)) {
            $this->view('auth/register', [
                'errors' => $errors,
                'old' => $old
            ]);
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $created = $userModel->createUser([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'password_hash' => $passwordHash,
            'university_role' => $universityRole,
            'verification_status' => 'pending',
            'account_status' => 'active'
        ]);

        if (!$created) {
            $this->view('auth/register', [
                'errors' => ['general' => 'Registration failed. Please try again.'],
                'old' => $old
            ]);
            return;
        }

        $newUser = $userModel->findByEmail($email);

        $_SESSION['user_id'] = $newUser['id'];
        $_SESSION['user_first_name'] = $newUser['first_name'];
        $_SESSION['user_role'] = $newUser['university_role'];
        $this->redirect('');
    }


    public function loginForm(): void
    {
        if (isset($_SESSION['user_id'])) {
            $this->redirect('');
        }
        $this->view('auth/login', [
            'title' => 'Login | Lynxloop',
            'errors' => [],
            'old' => []
        ]);
    }

    
public function login(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('login');
    }

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];
    $old = ['email' => $email];

    if ($email === '') {
        $errors['email'] = 'Email is required.';
    }

    if ($password === '') {
        $errors['password'] = 'Password is required.';
    }

    if (!empty($errors)) {
        $this->view('auth/login', [
            'errors' => $errors,
            'old' => $old
        ]);
        return;
    }

    $userModel = new User();
    $user = $userModel->findByEmail($email);

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $this->view('auth/login', [
            'errors' => ['general' => 'Invalid email or password.'],
            'old' => $old
        ]);
        return;
    }

    if ($user['account_status'] !== 'active') {
        $this->view('auth/login', [
            'errors' => ['general' => 'Your account is not active.'],
            'old' => $old
        ]);
        return;
    }

    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_first_name'] = $user['first_name'];
    $_SESSION['user_role'] = $user['university_role'];
    
    $this->redirect('/dashboard');
}

    public function logout(): void
    {
        $_SESSION = [];

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();
        $this->redirect('/login');
    }
}