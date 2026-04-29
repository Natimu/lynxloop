<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Listing;
use App\Models\Tables;
use App\Models\Favorite;
use App\Models\Message;
use App\Models\PriceHistory;
use App\Models\SavedSearch;
use App\Models\User;

class ListingsController extends Controller
{
    private const MAX_IMAGE_COUNT = 6;
    private const MAX_IMAGE_SIZE_BYTES = 5242880;
    private const ALLOWED_IMAGE_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

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
        $uploadedImages = $this->normalizeUploadedFiles($_FILES['images'] ?? null);

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

        $imageError = $this->validateImages($uploadedImages);
        if ($imageError !== null) {
            $errors['images'] = $imageError;
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

        $storedImagePaths = [];

        try {
            $storedImagePaths = $this->storeUploadedImages($uploadedImages);

            $this->listingModel->createListingWithImages([
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
            ], $storedImagePaths);
        } catch (Throwable $exception) {
            $this->deleteStoredImages($storedImagePaths);
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

    private function normalizeUploadedFiles(mixed $images): array
    {
        if (!is_array($images) || !isset($images['name']) || !is_array($images['name'])) {
            return [];
        }

        $files = [];

        foreach ($images['name'] as $index => $name) {
            $tmpName = $images['tmp_name'][$index] ?? '';
            if ($name === '' && $tmpName === '') {
                continue;
            }

            $files[] = [
                'name' => (string) $name,
                'type' => (string) ($images['type'][$index] ?? ''),
                'tmp_name' => (string) $tmpName,
                'error' => (int) ($images['error'][$index] ?? UPLOAD_ERR_NO_FILE),
                'size' => (int) ($images['size'][$index] ?? 0),
            ];
        }

        return $files;
    }

    private function validateImages(array $images): ?string
    {
        if ($images === []) {
            return 'Upload at least 1 image.';
        }

        if (count($images) > self::MAX_IMAGE_COUNT) {
            return 'Upload no more than 6 images.';
        }

        foreach ($images as $image) {
            if (($image['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
                return 'One or more images failed to upload.';
            }

            if (($image['size'] ?? 0) <= 0) {
                return 'Uploaded images must not be empty.';
            }

            if (($image['size'] ?? 0) > self::MAX_IMAGE_SIZE_BYTES) {
                return 'Each image must be 5 MB or smaller.';
            }

            $mimeType = $this->detectMimeType((string) ($image['tmp_name'] ?? ''));
            if ($mimeType === null || !array_key_exists($mimeType, self::ALLOWED_IMAGE_MIME_TYPES)) {
                return 'Only JPG, PNG, and WEBP images are allowed.';
            }
        }

        return null;
    }

    private function storeUploadedImages(array $images): array
    {
        $uploadDirectory = $this->uploadDirectory();
        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            throw new RuntimeException('Unable to create listing upload directory.');
        }

        $storedImagePaths = [];

        try {
            foreach ($images as $image) {
                $mimeType = $this->detectMimeType((string) $image['tmp_name']);
                if ($mimeType === null) {
                    throw new RuntimeException('Unable to determine uploaded image type.');
                }

                $extension = self::ALLOWED_IMAGE_MIME_TYPES[$mimeType] ?? null;
                if ($extension === null) {
                    throw new RuntimeException('Unsupported image type uploaded.');
                }

                $fileName = bin2hex(random_bytes(16)) . '.' . $extension;
                $destination = $uploadDirectory . DIRECTORY_SEPARATOR . $fileName;

                if (!move_uploaded_file((string) $image['tmp_name'], $destination)) {
                    throw new RuntimeException('Failed to move uploaded image.');
                }

                $storedImagePaths[] = '/uploads/listings/' . $fileName;
            }
        } catch (Throwable $exception) {
            $this->deleteStoredImages($storedImagePaths);
            throw $exception;
        }

        return $storedImagePaths;
    }

    private function deleteStoredImages(array $storedImagePaths): void
    {
        foreach ($storedImagePaths as $storedImagePath) {
            $absolutePath = dirname(__DIR__, 2) . '/public' . $storedImagePath;
            if (is_file($absolutePath)) {
                unlink($absolutePath);
            }
        }
    }

    private function uploadDirectory(): string
    {
        return dirname(__DIR__, 2) . '/public/uploads/listings';
    }

    private function detectMimeType(string $tmpFile): ?string
    {
        if ($tmpFile === '' || !is_file($tmpFile)) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo === false) {
            return null;
        }

        $mimeType = finfo_file($finfo, $tmpFile) ?: null;
        finfo_close($finfo);

        return is_string($mimeType) ? $mimeType : null;
    }

    // ── Listing Detail Page ─────────────────────────────────

    public function show(): void
    {
        $id = (int) ($_GET['id'] ?? 0);

        if ($id <= 0) {
            http_response_code(404);
            echo '404 - Listing not found';
            return;
        }

        $listing = $this->listingModel->getDetailWithImages($id);

        if (!$listing) {
            http_response_code(404);
            echo '404 - Listing not found';
            return;
        }

        $similar = $this->listingModel->getSimilar($id, (int) $listing['category_id'], 4);
        $priceDrop = (new PriceHistory())->getLatestDrop($id);

        $isLoggedIn = isset($_SESSION['user_id']);
        $isFavorited = false;
        $isOwner = false;
        $canBump = false;

        if ($isLoggedIn) {
            $userId = (int) $_SESSION['user_id'];
            $isFavorited = (new Favorite())->find($userId, $id) !== null;
            $isOwner = (int) $listing['user_id'] === $userId;
            $canBump = $isOwner && $this->listingModel->canBump($id);
        }

        $this->view('listings/show', [
            'title' => htmlspecialchars($listing['title']) . ' | Lynxloop',
            'isLoggedIn' => $isLoggedIn,
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'listing' => $listing,
            'similar' => $similar,
            'priceDrop' => $priceDrop,
            'isFavorited' => $isFavorited,
            'isOwner' => $isOwner,
            'canBump' => $canBump,
            'conditionOptions' => $this->conditionOptions(),
        ]);
    }

    // ── Bump ────────────────────────────────────────────────

    public function bump(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }

        $listingId = (int) ($_POST['listing_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];

        if ($this->listingModel->bump($listingId, $userId)) {
            $_SESSION['flash_success'] = 'Listing bumped to the top!';
        } else {
            $_SESSION['flash_error'] = 'You can only bump once every 24 hours.';
        }

        $this->redirect('/listings/show?id=' . $listingId);
    }

    // ── Favorite Toggle ─────────────────────────────────────

    public function toggleFavorite(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'POST required'], 405);
        }

        $listingId = (int) ($_POST['listing_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];

        if ($listingId <= 0) {
            $this->json(['error' => 'Invalid listing'], 400);
        }

        $favoriteModel = new Favorite();
        $isFavorited = $favoriteModel->toggle($userId, $listingId);

        $this->json(['favorited' => $isFavorited]);
    }

    // ── Quick Message ("Still available?") ──────────────────

    public function quickMessage(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }

        $listingId = (int) ($_POST['listing_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];
        $message = trim($_POST['message'] ?? 'Hey, is this still available?');

        if ($listingId <= 0) {
            $_SESSION['flash_error'] = 'Invalid listing.';
            $this->redirect('/dashboard');
        }

        $listing = $this->listingModel->findById($listingId);

        if (!$listing) {
            $_SESSION['flash_error'] = 'Listing not found.';
            $this->redirect('/dashboard');
        }

        $ownerId = (int) $listing['user_id'];

        if ($ownerId === $userId) {
            $_SESSION['flash_error'] = 'You cannot message yourself.';
            $this->redirect('/listings/show?id=' . $listingId);
        }

        $messageModel = new Message();
        $messageModel->sendQuickMessage($userId, $ownerId, $listingId, $message);

        // Update seller's response time cache
        (new User())->updateResponseTime($ownerId);

        $_SESSION['flash_success'] = 'Message sent to the seller!';
        $this->redirect('/listings/show?id=' . $listingId);
    }

    // ── Search ──────────────────────────────────────────────

    public function search(): void
    {
        $query = trim($_GET['q'] ?? '');
        $categoryId = !empty($_GET['category_id']) ? (int) $_GET['category_id'] : null;

        $results = [];
        if ($query !== '') {
            $results = $this->listingModel->search($query, $categoryId);
        }

        $this->view('listings/search', [
            'title' => 'Search | Lynxloop',
            'isLoggedIn' => isset($_SESSION['user_id']),
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'query' => $query,
            'categoryId' => $categoryId,
            'results' => $results,
            'categoryOptions' => $this->tablesModel->categoryOptions(),
        ]);
    }

    // ── Saved Searches ──────────────────────────────────────

    public function saveSearch(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/listings/search');
        }

        $query = trim($_POST['query'] ?? '');
        $categoryId = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $userId = (int) $_SESSION['user_id'];

        if ($query === '') {
            $_SESSION['flash_error'] = 'Enter a search term to save.';
            $this->redirect('/listings/search');
        }

        $savedSearchModel = new SavedSearch();
        $savedSearchModel->createSearch($userId, $query, $categoryId);

        $_SESSION['flash_success'] = 'Search alert saved! You will be notified when matching listings appear.';
        $this->redirect('/listings/search?q=' . urlencode($query));
    }

    public function deleteSavedSearch(): void
    {
        Auth::requireLogin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/dashboard');
        }

        $searchId = (int) ($_POST['search_id'] ?? 0);
        $userId = (int) $_SESSION['user_id'];

        $savedSearchModel = new SavedSearch();
        $savedSearchModel->deleteSearch($searchId, $userId);

        $_SESSION['flash_success'] = 'Search alert removed.';
        $this->redirect('/dashboard');
    }
}
