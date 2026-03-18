<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FileBrowserSortTest extends TestCase
{
    use RefreshDatabase;

    public function test_files_index_sorts_files_by_size_descending(): void
    {
        $user = User::factory()->create();

        UserFile::query()->create([
            'user_id' => $user->id,
            'folder_id' => null,
            'original_name' => 'small.txt',
            'stored_name' => 'small.txt',
            'disk' => 'local',
            'path' => 'uploads/small.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size' => 10,
            'hash' => str_repeat('1', 64),
        ]);

        UserFile::query()->create([
            'user_id' => $user->id,
            'folder_id' => null,
            'original_name' => 'large.txt',
            'stored_name' => 'large.txt',
            'disk' => 'local',
            'path' => 'uploads/large.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size' => 999,
            'hash' => str_repeat('2', 64),
        ]);

        $this->actingAs($user)
            ->get('/files?sort_by=size&sort_direction=desc')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Files/Index')
                ->where('sort.by', 'size')
                ->where('sort.direction', 'desc')
                ->where('files.0.original_name', 'large.txt')
                ->where('files.1.original_name', 'small.txt')
            );
    }
}
