<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Listing;
use App\Models\Tables;
use Throwable;

class ListingsController extends Controller
{
    private Listing $listingModel;
    private Tables $tablesModel;

    public function __construct()
    {
        $this->listingModel = new Listing();
        $this->tablesModel = new Tables();
    }

    public function create(): void
    {
        Auth::requireLogin();

        $this->view('listings/create', [
            'title' => 'New Listing | Lynxloop',
            'isLoggedIn' => isset($_SESSION['user_id']),
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'categoryOptions' => $this->tablesModel->categoryOptions(),
            'conditionOptions' => $this->conditionOptions(),
            'errors' => [],
            'old' => ['pickup_only' => 1, 'quantity' => 1],
        ]);
    }

    public function store(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/listings/create');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $categoryId = (int) ($_POST['category_id'] ?? 0);
        $condition = $_POST['item_condition'] ?? '';
        $priceRaw = trim($_POST['price'] ?? '');
        $quantity = (int) ($_POST['quantity'] ?? 1);
        $brand = trim($_POST['brand'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $tradeAllowed = isset($_POST['is_trade_allowed']) ? 1 : 0;
        $pickupOnly = isset($_POST['pickup_only']) ? 1 : 0;

        $old = [
            'title' => $title,
            'description' => $description,
            'category_id' => $categoryId,
            'item_condition' => $condition,
            'price' => $priceRaw,
            'quantity' => $quantity,
            'brand' => $brand,
            'location' => $location,
            'is_trade_allowed' => $tradeAllowed,
            'pickup_only' => $pickupOnly,
        ];

        $errors = [];

        if ($title === '') {
            $errors['title'] = 'Give your listing a name.';
        } elseif (mb_strlen($title) > 200) {
            $errors['title'] = 'Keep the title under 200 characters.';
        }

        if ($description === '') {
            $errors['description'] = 'Describe what you are offering.';
        }

        $categories = array_column($this->tablesModel->categoryOptions(), 'name', 'id');
        if ($categoryId <= 0 || !array_key_exists($categoryId, $categories)) {
            $errors['category_id'] = 'Select a category.';
        }

        $conditions = array_keys($this->conditionOptions());
        if (!in_array($condition, $conditions, true)) {
            $errors['item_condition'] = 'Choose a valid condition.';
        }

        $price = null;
        if ($priceRaw !== '') {
            if (!is_numeric($priceRaw) || (float) $priceRaw < 0) {
                $errors['price'] = 'Enter a valid price or leave blank for trades.';
            } else {
                $price = number_format((float) $priceRaw, 2, '.', '');
            }
        }

        if ($quantity < 1) {
            $errors['quantity'] = 'Quantity must be at least 1.';
        }

        if (!empty($brand) && mb_strlen($brand) > 100) {
            $errors['brand'] = 'Brand name is too long.';
        }

        if (!empty($location) && mb_strlen($location) > 150) {
            $errors['location'] = 'Location is too long.';
        }

        if (!empty($errors)) {
            $this->view('listings/create', [
                'title' => 'New Listing | Lynxloop',
                'isLoggedIn' => true,
                'firstName' => $_SESSION['user_first_name'] ?? null,
                'categoryOptions' => $this->tablesModel->categoryOptions(),
                'conditionOptions' => $this->conditionOptions(),
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        try {
            $this->listingModel->createListing([
                'user_id' => (int) $_SESSION['user_id'],
                'category_id' => $categoryId,
                'title' => $title,
                'description' => $description,
                'item_condition' => $condition,
                'price' => $price,
                'is_trade_allowed' => $tradeAllowed,
                'quantity' => $quantity,
                'brand' => $brand !== '' ? $brand : null,
                'location' => $location !== '' ? $location : null,
                'status' => 'pending',
                'pickup_only' => $pickupOnly,
            ]);
        } catch (Throwable $exception) {
            error_log('Listing creation failed: ' . $exception->getMessage());
            $errors['general'] = 'Something went wrong while saving your listing.';

            $this->view('listings/create', [
                'title' => 'New Listing | Lynxloop',
                'isLoggedIn' => true,
                'firstName' => $_SESSION['user_first_name'] ?? null,
                'categoryOptions' => $this->tablesModel->categoryOptions(),
                'conditionOptions' => $this->conditionOptions(),
                'errors' => $errors,
                'old' => $old,
            ]);
            return;
        }

        $_SESSION['flash_success'] = 'Listing submitted for review. We will notify you once it becomes visible.';
        $this->redirect('/dashboard');
    }

    private function categoryOptions(): array
    {
        return [
            ['id' => 1, 'label' => 'Textbooks & Courseware'],
            ['id' => 2, 'label' => 'Electronics & Gear'],
            ['id' => 3, 'label' => 'Apparel & Accessories'],
            ['id' => 4, 'label' => 'Furniture & Decor'],
            ['id' => 5, 'label' => 'Art & Studio Supplies'],
            ['id' => 6, 'label' => 'Experiences & Misc'],
            ['id' => 7, 'label' => 'Other'],
        ];
    }

    private function conditionOptions(): array
    {
        return [
            'new' => 'Brand New',
            'like_new' => 'Like New',
            'good' => 'Good',
            'fair' => 'Fair',
            'poor' => 'Well Loved',
        ];
    }
}
