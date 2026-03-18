<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FileBrowserPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_files_index_paginates_combined_folder_and_file_listing(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 12) as $index) {
            Folder::query()->create([
                'user_id' => $user->id,
                'parent_id' => null,
                'name' => sprintf('Folder %02d', $index),
                'path' => '/'.sprintf('Folder %02d', $index),
            ]);
        }

        foreach (range(1, 15) as $index) {
            UserFile::query()->create([
                'user_id' => $user->id,
                'folder_id' => null,
                'original_name' => sprintf('file-%02d.txt', $index),
                'stored_name' => sprintf('stored-%02d.txt', $index),
                'disk' => 'local',
                'path' => sprintf('uploads/%02d.txt', $index),
                'mime_type' => 'text/plain',
                'extension' => 'txt',
                'size' => 100 + $index,
                'hash' => str_pad((string) $index, 64, '0', STR_PAD_LEFT),
            ]);
        }

        $this->actingAs($user)
            ->get('/files?page=2')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Files/Index')
                ->has('folders', 0)
                ->has('files', 7)
                ->where('pagination.current_page', 2)
                ->where('pagination.last_page', 2)
                ->where('pagination.total', 27)
                ->where('pagination.from', 21)
                ->where('pagination.to', 27)
                ->where('files.0.original_name', 'file-09.txt')
                ->where('files.6.original_name', 'file-15.txt')
            );
    }
}
