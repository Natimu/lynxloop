<?php

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        // Logged-in users go straight to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }

        $this->viewRaw('Home/index', [
            'title' => 'LynxLoop — Campus Exchange',
            'isLoggedIn' => false,
            'userId' => null,
            'firstName' => null,
            'errors' => [],
            'old' => [],
            'activeTab' => 'login',
        ]);
    }
}
