<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use App\Repositories\FolderRepository;
use App\Repositories\UserFileRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FolderService
{
    public function __construct(
        private readonly FolderRepository $folderRepository,
        private readonly UserFileRepository $userFileRepository,
    ) {
    }

    public function create(User $user, string $name, ?int $parentId): Folder
    {
        $parent = $this->folderRepository->findUserFolderById($user->id, $parentId);

        if ($parentId !== null && $parent === null) {
            throw ValidationException::withMessages([
                'parent_id' => '上層資料夾不存在。',
            ]);
        }

        if ($this->folderRepository->existsSiblingName($user->id, $parent?->id, $name)) {
            throw ValidationException::withMessages([
                'name' => '同一層已存在相同名稱的資料夾。',
            ]);
        }

        $path = $parent?->path
            ? "{$parent->path}/{$name}"
            : $name;

        return DB::transaction(fn () => $this->folderRepository->create([
            'user_id' => $user->id,
            'parent_id' => $parent?->id,
            'name' => $name,
            'path' => $path,
        ]));
    }

    public function delete(User $user, int $folderId): void
    {
        $folder = $this->folderRepository->findUserFolderById($user->id, $folderId);

        if ($folder === null) {
            throw ValidationException::withMessages([
                'folder' => '找不到要刪除的資料夾。',
            ]);
        }

        if ($this->folderRepository->hasChildren($folder->id) || $this->userFileRepository->existsInFolder($folder->id)) {
            throw ValidationException::withMessages([
                'folder' => '資料夾內仍有資料，請先清空後再刪除。',
            ]);
        }

        DB::transaction(fn () => $this->folderRepository->delete($folder));
    }
}
