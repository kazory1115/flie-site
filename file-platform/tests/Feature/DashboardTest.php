<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_displays_storage_metrics_and_recent_uploads(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $contractsFolder = Folder::query()->create([
            'user_id' => $user->id,
            'parent_id' => null,
            'name' => 'Contracts',
            'path' => '/Contracts',
        ]);

        Folder::query()->create([
            'user_id' => $user->id,
            'parent_id' => null,
            'name' => 'Invoices',
            'path' => '/Invoices',
        ]);

        UserFile::query()->create([
            'user_id' => $user->id,
            'folder_id' => $contractsFolder->id,
            'original_name' => 'agreement.pdf',
            'stored_name' => 'agreement-1.pdf',
            'disk' => 'local',
            'path' => 'uploads/agreement-1.pdf',
            'mime_type' => 'application/pdf',
            'extension' => 'pdf',
            'size' => 2048,
            'hash' => str_repeat('a', 64),
            'created_at' => now()->subMinute(),
            'updated_at' => now()->subMinute(),
        ]);

        UserFile::query()->create([
            'user_id' => $user->id,
            'folder_id' => null,
            'original_name' => 'avatar.png',
            'stored_name' => 'avatar-1.png',
            'disk' => 'local',
            'path' => 'uploads/avatar-1.png',
            'mime_type' => 'image/png',
            'extension' => 'png',
            'size' => 1024,
            'hash' => str_repeat('b', 64),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        UserFile::query()->create([
            'user_id' => $otherUser->id,
            'folder_id' => null,
            'original_name' => 'other-user.txt',
            'stored_name' => 'other-user-1.txt',
            'disk' => 'local',
            'path' => 'uploads/other-user-1.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size' => 999999,
            'hash' => str_repeat('c', 64),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('metrics.total_files', 2)
                ->where('metrics.total_folders', 2)
                ->where('metrics.used_bytes', 3072)
                ->where('metrics.used_bytes_label', '3.0 KB')
                ->where('quota.limit_bytes', 1073741824)
                ->where('quota.used_bytes', 3072)
                ->where('quota.remaining_bytes', 1073738752)
                ->where('quota.usage_percentage', 0.0)
                ->has('recentUploads', 2)
                ->where('recentUploads.0.original_name', 'avatar.png')
                ->where('recentUploads.0.folder_path', null)
                ->where('recentUploads.1.original_name', 'agreement.pdf')
                ->where('recentUploads.1.folder_path', '/Contracts')
            );
    }
}
