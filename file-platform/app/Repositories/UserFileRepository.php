<?php

namespace App\Repositories;

use App\Models\UserFile;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFileRepository
{
    public function countByUserId(int $userId): int
    {
        return UserFile::query()
            ->where('user_id', $userId)
            ->count();
    }

    public function sumSizeByUserId(int $userId): int
    {
        return (int) UserFile::query()
            ->where('user_id', $userId)
            ->sum('size');
    }

    public function getRecentByUserId(int $userId, int $limit = 5): Collection
    {
        return UserFile::query()
            ->with([
                'folder' => fn (BelongsTo $query) => $query->select(['id', 'name', 'path']),
            ])
            ->where('user_id', $userId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getByFolderId(int $userId, ?int $folderId): Collection
    {
        return UserFile::query()
            ->where('user_id', $userId)
            ->where('folder_id', $folderId)
            ->orderBy('original_name')
            ->get();
    }

    public function countByFolderId(int $userId, ?int $folderId, ?string $search = null): int
    {
        return $this->byFolderIdQuery($userId, $folderId, $search)->count();
    }

    public function getByFolderIdPageSlice(int $userId, ?int $folderId, int $offset, int $limit, ?string $search = null, string $sortBy = 'name', string $sortDirection = 'asc'): Collection
    {
        if ($limit <= 0) {
            return new Collection();
        }

        return $this->byFolderIdQuery($userId, $folderId, $search, $sortBy, $sortDirection)
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    private function byFolderIdQuery(int $userId, ?int $folderId, ?string $search = null, string $sortBy = 'name', string $sortDirection = 'asc')
    {
        $direction = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
        $query = UserFile::query()
            ->where('user_id', $userId)
            ->where('folder_id', $folderId);

        if ($search !== null && $search !== '') {
            $query->where('original_name', 'like', '%'.$search.'%');
        }

        $column = match ($sortBy) {
            'created_at' => 'created_at',
            'size' => 'size',
            default => 'original_name',
        };

        $query->orderBy($column, $direction)->orderBy('original_name');

        return $query;
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

    public function existsDuplicateNameExcept(int $userId, ?int $folderId, string $originalName, int $ignoredFileId): bool
    {
        return UserFile::query()
            ->where('user_id', $userId)
            ->where('folder_id', $folderId)
            ->whereKeyNot($ignoredFileId)
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

    public function save(UserFile $file): void
    {
        $file->save();
    }
}
