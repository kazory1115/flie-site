<?php

namespace App\Repositories;

use App\Models\Folder;
use Illuminate\Database\Eloquent\Collection;

class FolderRepository
{
    public function countByUserId(int $userId): int
    {
        return Folder::query()
            ->where('user_id', $userId)
            ->count();
    }

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

    public function countChildrenByParentId(int $userId, ?int $parentId, ?string $search = null): int
    {
        return $this->childrenByParentIdQuery($userId, $parentId, $search)->count();
    }

    public function getChildrenPageSlice(int $userId, ?int $parentId, int $offset, int $limit, ?string $search = null, string $sortBy = 'name', string $sortDirection = 'asc'): Collection
    {
        if ($limit <= 0) {
            return new Collection();
        }

        return $this->childrenByParentIdQuery($userId, $parentId, $search, $sortBy, $sortDirection)
            ->offset($offset)
            ->limit($limit)
            ->get();
    }

    private function childrenByParentIdQuery(int $userId, ?int $parentId, ?string $search = null, string $sortBy = 'name', string $sortDirection = 'asc')
    {
        $direction = strtolower($sortDirection) === 'desc' ? 'desc' : 'asc';
        $query = Folder::query()
            ->where('user_id', $userId)
            ->where('parent_id', $parentId);

        if ($search !== null && $search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        if ($sortBy === 'created_at') {
            $query->orderBy('created_at', $direction)->orderBy('name');
        } elseif ($sortBy === 'name') {
            $query->orderBy('name', $direction);
        } else {
            $query->orderBy('name');
        }

        return $query;
    }

    public function getAllByUserId(int $userId): Collection
    {
        return Folder::query()
            ->where('user_id', $userId)
            ->orderBy('path')
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

    public function existsSiblingNameExcept(int $userId, ?int $parentId, string $name, int $ignoredFolderId): bool
    {
        return Folder::query()
            ->where('user_id', $userId)
            ->where('parent_id', $parentId)
            ->whereKeyNot($ignoredFolderId)
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

    public function save(Folder $folder): void
    {
        $folder->save();
    }

    public function getDescendantsByPathPrefix(int $userId, string $pathPrefix): Collection
    {
        $escapedPathPrefix = addcslashes($pathPrefix, '\\%_');

        return Folder::query()
            ->where('user_id', $userId)
            ->whereRaw("path LIKE ? ESCAPE '\\'", [$escapedPathPrefix.'/%'])
            ->orderBy('path')
            ->get();
    }
}
