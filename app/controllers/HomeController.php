<?php

use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
      $data = [
        'title' => 'Home | Lynxloop',
        'isLoggedIn' => isset($_SESSION['user_id']),
        'currentUser' => $_SESSION['user_name'] ?? null,
        'userId' => $_SESSION['user_id'] ?? null,
        'firstName' => $_SESSION['user_first_name'] ?? null
        ];
        
        $this->view('home/index', $data);
    }
}