<?php

namespace Tests\Feature;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderRenameTest extends TestCase
{
    use RefreshDatabase;

    public function test_folder_rename_updates_descendant_paths(): void
    {
        $user = User::factory()->create();

        $parent = Folder::query()->create([
            'user_id' => $user->id,
            'parent_id' => null,
            'name' => 'Projects',
            'path' => 'Projects',
        ]);

        $child = Folder::query()->create([
            'user_id' => $user->id,
            'parent_id' => $parent->id,
            'name' => '2026',
            'path' => 'Projects/2026',
        ]);

        $response = $this->actingAs($user)->patch("/folders/{$parent->id}", [
            'name' => 'Clients',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('Clients', $parent->fresh()->name);
        $this->assertSame('Clients', $parent->fresh()->path);
        $this->assertSame('Clients/2026', $child->fresh()->path);
    }
}
