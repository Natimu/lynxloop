<?php

declare(strict_types=1);

use App\Core\Auth;
use App\Core\Controller;

class DashboardController extends Controller
{
    public function dashboard(): void
    {
        Auth::requireLogin();

        $data = [
            'title' => 'Home | Lynxloop',
            'isLoggedIn' => isset($_SESSION['user_id']),
            'currentUser' => $_SESSION['user_name'] ?? null,
            'userId' => $_SESSION['user_id'] ?? null,
            'firstName' => $_SESSION['user_first_name'] ?? null,
            'sections' => $this->buildListingSections(),
        ];

        $this->view('partials/dashboard', $data);
    }

    /**
     * TODO: Replace mocked content with database-backed listings once models exist.
     *
     * @return array<int, array<string, mixed>>
     */
    private function buildListingSections(): array
    {
        return [
            [
                'slug' => 'dashboard',
                'label' => "Today's Highlights",
                'subheading' => 'Fresh drops from across the marketplace.',
                'listings' => [
                    [
                        'title' => 'Organic Chemistry Essentials Bundle',
                        'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Three gently used textbooks with fresh margin notes and laminated quick-reference sheets.',
                        'seller' => [
                            'name' => 'Alana Reid',
                            'avatar' => 'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Biochem · Class of 2025',
                        ],
                    ],
                    [
                        'title' => 'Retro Vinyl Desk Speakers',
                        'image' => 'https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Hand-built ash cabinets with aux + Bluetooth, perfect for studio corners.',
                        'seller' => [
                            'name' => 'Mateo Vega',
                            'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Audio Lab Volunteer',
                        ],
                    ],
                    [
                        'title' => 'Reclaimed Canvas Jacket',
                        'image' => 'https://images.unsplash.com/photo-1521572267360-ee0c2909d518?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Indigo-dyed jacket with reinforced seams and hidden phone pocket.',
                        'seller' => [
                            'name' => 'Kai Okafor',
                            'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Fashion Cohort',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'textbooks',
                'label' => 'Textbooks',
                'subheading' => 'Verified academic bundles ready for next semester.',
                'listings' => [
                    [
                        'title' => 'Data Structures in Practice',
                        'image' => 'https://images.unsplash.com/photo-1457694587812-e8bf29a43845?auto=format&fit=crop&w=900&q=80',
                        'description' => 'CS205 workbook + flashcards with color-coded tabs.',
                        'seller' => [
                            'name' => 'Serena Holt',
                            'avatar' => 'https://images.unsplash.com/photo-1544723795-432537dc6087?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Tutor · Library West',
                        ],
                    ],
                    [
                        'title' => 'Microeconomics Lab Notes',
                        'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Printed slides annotated with exam tips from Fall cohort.',
                        'seller' => [
                            'name' => 'Noah Bennett',
                            'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Economics Mentor',
                        ],
                    ],
                    [
                        'title' => 'Studio Art Sketch Pads',
                        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Acid-free pads (18x24) with bonus charcoal set.',
                        'seller' => [
                            'name' => 'Ivy Chen',
                            'avatar' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Fine Arts · Studio B',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'electronics',
                'label' => 'Electronics',
                'subheading' => 'Refreshed tech with campus warranties where possible.',
                'listings' => [
                    [
                        'title' => 'USB-C Portable Monitor',
                        'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=900&q=80',
                        'description' => '15.6” matte panel with magnetic case stand and calibration sheet.',
                        'seller' => [
                            'name' => 'Esteban Flores',
                            'avatar' => 'https://images.unsplash.com/photo-1521119989659-a83eee488004?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Hardware Club',
                        ],
                    ],
                    [
                        'title' => 'Analog Film Scanner Rig',
                        'image' => 'https://images.unsplash.com/photo-1484704849700-09d5f5c0e9ce?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Dual-LED rail system for digitizing 35mm archives.',
                        'seller' => [
                            'name' => 'Morgan Blake',
                            'avatar' => 'https://images.unsplash.com/photo-1546456073-92b9f0a8d1d6?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Media Lab',
                        ],
                    ],
                    [
                        'title' => 'Quadcopter Starter Kit',
                        'image' => 'https://images.unsplash.com/photo-1505740106531-4243f3831c55?auto=format&fit=crop&w=900&q=80',
                        'description' => '3D printed frame, spare props, and flight controller presets.',
                        'seller' => [
                            'name' => 'Priya Raman',
                            'avatar' => 'https://images.unsplash.com/photo-1544723795-432537dc6087?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Aerial Robotics',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'apparel',
                'label' => 'Apparel',
                'subheading' => 'Upcycled fits for pop-ups and presentation days.',
                'listings' => [
                    [
                        'title' => 'Convertible Utility Tote',
                        'image' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Waxed canvas tote that morphs into a backpack within seconds.',
                        'seller' => [
                            'name' => 'Julian Park',
                            'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Textiles Lab',
                        ],
                    ],
                    [
                        'title' => 'Hand Loomed Scarf Set',
                        'image' => 'https://images.unsplash.com/photo-1487412720507-e7ab37603c6f?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Pair of plant-dyed scarves with gradient weaves.',
                        'seller' => [
                            'name' => 'Nadia Rivers',
                            'avatar' => 'https://images.unsplash.com/photo-1544723795-3fb6469f5b39?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Fashion Research',
                        ],
                    ],
                    [
                        'title' => 'Studio Sneakers Prototype',
                        'image' => 'https://images.unsplash.com/photo-1500530855697-b586d89ba3ee?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Recycled rubber soles blended with cork flecks for comfort.',
                        'seller' => [
                            'name' => 'Eli Turner',
                            'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Industrial Design',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'saved',
                'label' => 'Saved Items',
                'subheading' => 'Listings you flagged for follow-up conversations.',
                'listings' => [
                    [
                        'title' => 'Collaborative Whiteboard Wall',
                        'image' => 'https://images.unsplash.com/photo-1487017159836-4e23ece2e4cf?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Peel-and-stick kit with low-odor markers and mounting squeegee.',
                        'seller' => [
                            'name' => 'Autumn Lee',
                            'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Innovation Hub',
                        ],
                    ],
                    [
                        'title' => 'Dual Lens Field Camera',
                        'image' => 'https://images.unsplash.com/photo-1453728013993-6d66e9c9123a?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Custom leather straps plus ND filter pack.',
                        'seller' => [
                            'name' => 'Sofia Marin',
                            'avatar' => 'https://images.unsplash.com/photo-1546456073-92b9f0a8d1d6?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Film Collective',
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'contacted',
                'label' => 'Contacted Sellers',
                'subheading' => 'Threads awaiting responses or next steps.',
                'listings' => [
                    [
                        'title' => 'Zero-Gravity Desk Chair',
                        'image' => 'https://images.unsplash.com/photo-1493666438817-866a91353ca9?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Breathable mesh with adjustable lumbar kit and rolling mat.',
                        'seller' => [
                            'name' => 'Darius Cole',
                            'avatar' => 'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Ergo Society',
                        ],
                    ],
                    [
                        'title' => 'Mini Hydroponic Grow Bar',
                        'image' => 'https://images.unsplash.com/photo-1500534314209-a25ddb2bd429?auto=format&fit=crop&w=900&q=80',
                        'description' => 'Self-watering planter great for studio kitchens.',
                        'seller' => [
                            'name' => 'Lena Ortiz',
                            'avatar' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?auto=format&fit=facearea&w=120&h=120&q=80',
                            'status' => 'Greenhouse Assoc.',
                        ],
                    ],
                ],
            ],
        ];
    }
}
