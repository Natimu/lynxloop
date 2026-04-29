<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Listing;
use App\Models\Favorite;
use App\Models\SavedSearch;
use App\Models\PriceHistory;
use App\Models\Message;
use App\Models\Tables;

class DashboardController extends Controller
{
    private Listing $listingModel;
    private Tables $tablesModel;

    public function __construct()
    {
        $this->listingModel = new Listing();
        $this->tablesModel = new Tables();
    }

    public function dashboard(): void
    {
        Auth::requireLogin();

        $userId = (int) $_SESSION['user_id'];
        $favoriteModel = new Favorite();
        $savedSearchModel = new SavedSearch();
        $messageModel = new Message();
        $priceHistoryModel = new PriceHistory();

        // Get categories for tabs
        $categories = $this->tablesModel->categoryOptions();

        // Build sections from real data
        $sections = [];

        // Today's Highlights — all active listings, sorted by bump then newest
        $highlights = $this->listingModel->getActiveByCategory(null, 12);
        $this->attachPriceDrops($highlights, $priceHistoryModel);
        $sections[] = [
            'slug' => 'dashboard',
            'label' => "Today's Highlights",
            'subheading' => 'Fresh drops from across the marketplace.',
            'listings' => $highlights,
        ];

        // Category tabs
        foreach ($categories as $cat) {
            $catListings = $this->listingModel->getActiveByCategory((int) $cat['id'], 6);
            $this->attachPriceDrops($catListings, $priceHistoryModel);
            $sections[] = [
                'slug' => strtolower(preg_replace('/[^a-zA-Z0-9]/', '-', $cat['name'])),
                'label' => $cat['name'],
                'subheading' => '',
                'listings' => $catListings,
            ];
        }

        // My Listings
        $myListings = $this->listingModel->getByUser($userId);
        $sections[] = [
            'slug' => 'my-listings',
            'label' => 'My Listings',
            'subheading' => 'Listings you have published.',
            'listings' => $myListings,
        ];

        // Saved / Favorited items
        $favListings = $favoriteModel->getUserFavoritesWithListings($userId);
        $this->attachPriceDrops($favListings, $priceHistoryModel);
        $sections[] = [
            'slug' => 'saved',
            'label' => 'Saved Items',
            'subheading' => 'Listings you bookmarked for later.',
            'listings' => $favListings,
        ];

        // Saved searches
        $savedSearches = $savedSearchModel->getByUser($userId);

        // Unread message count
        $unreadCount = $messageModel->getUnreadCount($userId);

        $data = [
            'title' => 'Home | Lynxloop',
            'isLoggedIn' => true,
            'currentUser' => $_SESSION['user_name'] ?? null,
            'userId' => $userId,
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'sections' => $sections,
            'savedSearches' => $savedSearches,
            'unreadCount' => $unreadCount,
            'totalListings' => count($highlights),
        ];

        $this->view('partials/dashboard', $data);
    }

    /**
     * Attach price drop info to an array of listings (by reference).
     */
    private function attachPriceDrops(array &$listings, PriceHistory $priceHistoryModel): void
    {
        $ids = array_filter(array_column($listings, 'id'));
        if (empty($ids)) {
            return;
        }

        $drops = $priceHistoryModel->getLatestDropsForListings($ids);

        foreach ($listings as &$listing) {
            $lid = (int) ($listing['id'] ?? 0);
            if (isset($drops[$lid])) {
                $listing['price_drop'] = $drops[$lid];
            }
        }
        unset($listing);
    }
}
