<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FileBrowserSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_files_index_filters_current_folder_listing_by_search_keyword(): void
    {
        $user = User::factory()->create();

        Folder::query()->create([
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
            'folder_id' => null,
            'original_name' => 'contract-template.docx',
            'stored_name' => 'contract-template.docx',
            'disk' => 'local',
            'path' => 'uploads/contract-template.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'extension' => 'docx',
            'size' => 100,
            'hash' => str_repeat('1', 64),
        ]);

        UserFile::query()->create([
            'user_id' => $user->id,
            'folder_id' => null,
            'original_name' => 'invoice-2026.xlsx',
            'stored_name' => 'invoice-2026.xlsx',
            'disk' => 'local',
            'path' => 'uploads/invoice-2026.xlsx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'extension' => 'xlsx',
            'size' => 100,
            'hash' => str_repeat('2', 64),
        ]);

        $this->actingAs($user)
            ->get('/files?search=contract')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Files/Index')
                ->where('query.search', 'contract')
                ->has('folders', 1)
                ->where('folders.0.name', 'Contracts')
                ->has('files', 1)
                ->where('files.0.original_name', 'contract-template.docx')
                ->where('pagination.total', 2)
            );
    }
}
