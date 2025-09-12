<?php

namespace Database\Seeders;

use App\Models\Folder;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FolderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get admin user as folder creator
        $adminUser = User::where('username', 'admin')->first();

        // Get all units
        $units = Unit::all();

        // Define folder colors based on unit type
        $unitColors = [
            'ER' => '#dc2626',      // Red - Emergency
            'ICU' => '#7c2d12',     // Dark red - Critical
            'SUR' => '#059669',     // Green - Surgery
            'MED' => '#2563eb',     // Blue - Medicine
            'PED' => '#f59e0b',     // Yellow - Pediatrics
            'OBG' => '#ec4899',     // Pink - Women's health
            'RAD' => '#6366f1',     // Indigo - Radiology
            'LAB' => '#8b5cf6',     // Purple - Laboratory
            'PHR' => '#10b981',     // Emerald - Pharmacy
            'PTH' => '#f97316',     // Orange - Physical Therapy
        ];

        // Define folder icons based on unit type
        $unitIcons = [
            'ER' => 'ambulance',
            'ICU' => 'heart-pulse',
            'SUR' => 'scalpel',
            'MED' => 'stethoscope',
            'PED' => 'baby',
            'OBG' => 'heart',
            'RAD' => 'scan',
            'LAB' => 'flask-conical',
            'PHR' => 'pill',
            'PTH' => 'dumbbell',
        ];

        foreach ($units as $unit) {
            // Create parent folder for each unit
            $parentFolder = Folder::create([
                'name' => $unit->name,
                'slug' => Str::slug($unit->name),
                'description' => "Main folder for {$unit->description}",
                'parent_id' => null,
                'path' => null, // Will be set after creation
                'level' => 0,
                'color' => $unitColors[$unit->code] ?? '#6b7280',
                'icon' => $unitIcons[$unit->code] ?? 'folder',
                'is_active' => true,
                'created_by' => $adminUser->id,
                'updated_by' => $adminUser->id,
            ]);

            // Update path after creation
            $parentFolder->update(['path' => '/' . $parentFolder->id]);

            // Create common subfolders for each unit
            $subfolders = $this->getSubfoldersForUnit($unit->code);

            foreach ($subfolders as $subfolder) {
                $childFolder = Folder::create([
                    'name' => $subfolder['name'],
                    'slug' => Str::slug($subfolder['name']),
                    'description' => $subfolder['description'],
                    'parent_id' => $parentFolder->id,
                    'path' => null, // Will be set after creation
                    'level' => 1,
                    'color' => $subfolder['color'] ?? '#9ca3af',
                    'icon' => $subfolder['icon'] ?? 'folder',
                    'is_active' => true,
                    'created_by' => $adminUser->id,
                    'updated_by' => $adminUser->id,
                ]);

                // Update path after creation
                $childFolder->update(['path' => $parentFolder->path . '/' . $childFolder->id]);

                // Create some specific subfolders for certain units
                if (isset($subfolder['children'])) {
                    foreach ($subfolder['children'] as $grandchild) {
                        $grandchildFolder = Folder::create([
                            'name' => $grandchild['name'],
                            'slug' => Str::slug($grandchild['name']),
                            'description' => $grandchild['description'],
                            'parent_id' => $childFolder->id,
                            'path' => null,
                            'level' => 2,
                            'color' => $grandchild['color'] ?? '#d1d5db',
                            'icon' => $grandchild['icon'] ?? 'file-text',
                            'is_active' => true,
                            'created_by' => $adminUser->id,
                            'updated_by' => $adminUser->id,
                        ]);

                        // Update path after creation
                        $grandchildFolder->update(['path' => $childFolder->path . '/' . $grandchildFolder->id]);
                    }
                }
            }
        }
    }

    /**
     * Get subfolders configuration for each unit type
     */
    private function getSubfoldersForUnit(string $unitCode): array
    {
        $commonFolders = [
            [
                'name' => 'SOP (Standard Operating Procedures)',
                'description' => 'Standard operating procedures for the unit',
                'color' => '#3b82f6',
                'icon' => 'file-check',
            ],
            [
                'name' => 'Forms & Templates',
                'description' => 'Forms and document templates',
                'color' => '#8b5cf6',
                'icon' => 'file-text',
            ],
            [
                'name' => 'Reports',
                'description' => 'Monthly and annual reports',
                'color' => '#059669',
                'icon' => 'chart-bar',
                'children' => [
                    [
                        'name' => 'Monthly Reports',
                        'description' => 'Monthly unit reports',
                        'color' => '#10b981',
                        'icon' => 'calendar',
                    ],
                    [
                        'name' => 'Annual Reports',
                        'description' => 'Annual unit reports',
                        'color' => '#059669',
                        'icon' => 'calendar-range',
                    ],
                ]
            ],
            [
                'name' => 'Training Materials',
                'description' => 'Training and educational materials',
                'color' => '#f59e0b',
                'icon' => 'graduation-cap',
            ],
            [
                'name' => 'Policies',
                'description' => 'Unit policies and guidelines',
                'color' => '#dc2626',
                'icon' => 'shield-check',
            ],
        ];

        $specificFolders = [
            'ER' => [
                [
                    'name' => 'Triage Protocols',
                    'description' => 'Emergency triage protocols and guidelines',
                    'color' => '#dc2626',
                    'icon' => 'alert-triangle',
                ],
                [
                    'name' => 'Emergency Procedures',
                    'description' => 'Emergency medical procedures',
                    'color' => '#991b1b',
                    'icon' => 'zap',
                ],
            ],
            'ICU' => [
                [
                    'name' => 'Ventilator Protocols',
                    'description' => 'Ventilator management protocols',
                    'color' => '#7c2d12',
                    'icon' => 'wind',
                ],
                [
                    'name' => 'Critical Care Guidelines',
                    'description' => 'Critical care management guidelines',
                    'color' => '#991b1b',
                    'icon' => 'heart-pulse',
                ],
            ],
            'SUR' => [
                [
                    'name' => 'Surgical Protocols',
                    'description' => 'Surgical procedures and protocols',
                    'color' => '#059669',
                    'icon' => 'scalpel',
                ],
                [
                    'name' => 'OR Management',
                    'description' => 'Operating room management documents',
                    'color' => '#047857',
                    'icon' => 'building',
                ],
            ],
            'LAB' => [
                [
                    'name' => 'Test Procedures',
                    'description' => 'Laboratory test procedures and protocols',
                    'color' => '#8b5cf6',
                    'icon' => 'flask-conical',
                ],
                [
                    'name' => 'Quality Control',
                    'description' => 'Quality control documents and records',
                    'color' => '#7c3aed',
                    'icon' => 'check-circle',
                ],
            ],
            'RAD' => [
                [
                    'name' => 'Imaging Protocols',
                    'description' => 'Medical imaging protocols and procedures',
                    'color' => '#6366f1',
                    'icon' => 'scan',
                ],
                [
                    'name' => 'Equipment Manuals',
                    'description' => 'Radiology equipment manuals and guides',
                    'color' => '#4f46e5',
                    'icon' => 'settings',
                ],
            ],
            'PHR' => [
                [
                    'name' => 'Drug Information',
                    'description' => 'Drug information and formulary',
                    'color' => '#10b981',
                    'icon' => 'pill',
                ],
                [
                    'name' => 'Dispensing Procedures',
                    'description' => 'Medication dispensing procedures',
                    'color' => '#059669',
                    'icon' => 'package',
                ],
            ],
            'PED' => [
                [
                    'name' => 'Pediatric Protocols',
                    'description' => 'Pediatric care protocols and guidelines',
                    'color' => '#f59e0b',
                    'icon' => 'baby',
                ],
                [
                    'name' => 'Growth Charts',
                    'description' => 'Pediatric growth charts and references',
                    'color' => '#d97706',
                    'icon' => 'trending-up',
                ],
            ],
            'OBG' => [
                [
                    'name' => 'Delivery Protocols',
                    'description' => 'Delivery and maternity care protocols',
                    'color' => '#ec4899',
                    'icon' => 'heart',
                ],
                [
                    'name' => 'Gynecological Procedures',
                    'description' => 'Gynecological examination procedures',
                    'color' => '#db2777',
                    'icon' => 'user-check',
                ],
            ],
            'PTH' => [
                [
                    'name' => 'Therapy Protocols',
                    'description' => 'Physical therapy treatment protocols',
                    'color' => '#f97316',
                    'icon' => 'dumbbell',
                ],
                [
                    'name' => 'Exercise Programs',
                    'description' => 'Rehabilitation exercise programs',
                    'color' => '#ea580c',
                    'icon' => 'activity',
                ],
            ],
        ];

        // Merge common folders with unit-specific folders
        return array_merge($commonFolders, $specificFolders[$unitCode] ?? []);
    }
}
