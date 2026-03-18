<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FileRenameTest extends TestCase
{
    use RefreshDatabase;

    public function test_file_can_be_renamed(): void
    {
        $user = User::factory()->create();

        $file = UserFile::query()->create([
            'user_id' => $user->id,
            'folder_id' => null,
            'original_name' => 'old-name.txt',
            'stored_name' => 'stored-name.txt',
            'disk' => 'local',
            'path' => 'uploads/stored-name.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size' => 120,
            'hash' => str_repeat('a', 64),
        ]);

        $response = $this->actingAs($user)->patch("/files/{$file->id}", [
            'name' => 'new-name.txt',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertSame('new-name.txt', $file->fresh()->original_name);
    }
}
