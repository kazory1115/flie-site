<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileQuotaTest extends TestCase
{
    use RefreshDatabase;

    public function test_upload_is_blocked_when_user_quota_would_be_exceeded(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'quota_bytes' => 1024,
        ]);

        $response = $this->actingAs($user)
            ->from('/files')
            ->post('/files', [
                'file' => UploadedFile::fake()->create('too-large.txt', 2, 'text/plain'),
            ]);

        $response
            ->assertRedirect('/files')
            ->assertSessionHasErrors('file');

        $this->assertDatabaseCount('files', 0);
    }

    public function test_upload_is_allowed_when_user_has_remaining_quota(): void
    {
        Storage::fake('local');

        $user = User::factory()->create([
            'quota_bytes' => 4096,
        ]);

        $response = $this->actingAs($user)
            ->post('/files', [
                'file' => UploadedFile::fake()->create('fits.txt', 2, 'text/plain'),
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $this->assertDatabaseCount('files', 1);
        $this->assertNotNull(UserFile::query()->first());
    }
}
