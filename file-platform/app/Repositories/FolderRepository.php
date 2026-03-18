<?php

namespace App\Repositories;

use App\Models\Folder;
use Illuminate\Database\Eloquent\Collection;

class FolderRepository
{
    public function findUserFolderById(int $userId, ?int $folderId): ?Folder
    {
        if ($folderId === null) {
            return null;
        }

        return Folder::query()
            ->where('user_id', $userId)
            ->whereKey($folderId)
            ->first();
    }

    public function getChildrenByParentId(int $userId, ?int $parentId): Collection
    {
        return Folder::query()
            ->where('user_id', $userId)
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
    }

    public function existsSiblingName(int $userId, ?int $parentId, string $name): bool
    {
        return Folder::query()
            ->where('user_id', $userId)
            ->where('parent_id', $parentId)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->exists();
    }

    public function create(array $attributes): Folder
    {
        return Folder::query()->create($attributes);
    }

    public function hasChildren(int $folderId): bool
    {
        return Folder::query()->where('parent_id', $folderId)->exists();
    }

    public function delete(Folder $folder): void
    {
        $folder->delete();
    }
}
