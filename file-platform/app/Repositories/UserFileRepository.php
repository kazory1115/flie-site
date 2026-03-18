<?php

namespace App\Repositories;

use App\Models\UserFile;
use Illuminate\Database\Eloquent\Collection;

class UserFileRepository
{
    public function getByFolderId(int $userId, ?int $folderId): Collection
    {
        return UserFile::query()
            ->where('user_id', $userId)
            ->where('folder_id', $folderId)
            ->orderBy('original_name')
            ->get();
    }

    public function findUserFileById(int $userId, int $fileId): ?UserFile
    {
        return UserFile::query()
            ->where('user_id', $userId)
            ->whereKey($fileId)
            ->first();
    }

    public function existsDuplicateName(int $userId, ?int $folderId, string $originalName): bool
    {
        return UserFile::query()
            ->where('user_id', $userId)
            ->where('folder_id', $folderId)
            ->whereRaw('LOWER(original_name) = ?', [mb_strtolower($originalName)])
            ->exists();
    }

    public function create(array $attributes): UserFile
    {
        return UserFile::query()->create($attributes);
    }

    public function existsInFolder(int $folderId): bool
    {
        return UserFile::query()->where('folder_id', $folderId)->exists();
    }

    public function delete(UserFile $file): void
    {
        $file->delete();
    }
}
