<?php


use App\core\Auth;
use App\Core\Controller;

class DashboardController extends Controller
{
   public function index(): void
    {
        Auth::requireLogin();

        $this->view('dashboard', [
            'title' => 'Dashboard',
            'user' => Auth::user(),
        ]);
    }
    
    public function dashboard(): void 
    {
      $data = [
        'title' => 'Home | Lynxloop',
        'isLoggedIn' => isset($_SESSION['user_id']),
        'currentUser' => $_SESSION['user_name'] ?? null,
        'userId' => $_SESSION['user_id'] ?? null,
        'firstName' => $_SESSION['user_first_name'] ?? null
        ];
        
        $this->view('partials/dashboard', $data);
    }
}