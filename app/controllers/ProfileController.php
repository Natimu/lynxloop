<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Controller;
use App\Models\User;
use App\Models\Listing;
use App\Models\Favorite;
use App\Models\Message;

class ProfileController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $userId = (int) $_SESSION['user_id'];
        $userModel = new User();
        $listingModel = new Listing();
        $favoriteModel = new Favorite();
        $messageModel = new Message();

        $user = $userModel->getById($userId);

        if (!$user) {
            $_SESSION['flash_error'] = 'User not found.';
            $this->redirect('/dashboard');
        }

        $userListings = $listingModel->getByUser($userId);
        $favoriteCount = count($favoriteModel->getUserFavoriteIds($userId));
        $messageCount = $messageModel->getUnreadCount($userId);

        $this->view('profile/index', [
            'title' => 'Profile | Lynxloop',
            'isLoggedIn' => true,
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'user' => $user,
            'userListings' => $userListings,
            'favoriteCount' => $favoriteCount,
            'messageCount' => $messageCount,
        ]);
    }
}
