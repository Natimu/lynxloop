<?php

declare(strict_types=1);

use App\Core\Controller;

class PagesController extends Controller
{
    public function about(): void
    {
        $this->view('pages/about', [
            'title' => 'About | Lynxloop',
            'isLoggedIn' => isset($_SESSION['user_id']),
            'firstName' => $_SESSION['user_first_name'] ?? null,
        ]);
    }

    public function support(): void
    {
        $this->view('pages/support', [
            'title' => 'Support | Lynxloop',
            'isLoggedIn' => isset($_SESSION['user_id']),
            'firstName' => $_SESSION['user_first_name'] ?? null,
        ]);
    }
}
