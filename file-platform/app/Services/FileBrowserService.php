<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use App\Repositories\FolderRepository;
use App\Repositories\UserFileRepository;
use Illuminate\Validation\ValidationException;

class FileBrowserService
{
    public function __construct(
        private readonly FolderRepository $folderRepository,
        private readonly UserFileRepository $userFileRepository,
    ) {
    }

    public function buildIndexData(User $user, ?int $folderId): array
    {
        $currentFolder = $this->folderRepository->findUserFolderById($user->id, $folderId);

        if ($folderId !== null && $currentFolder === null) {
            throw ValidationException::withMessages([
                'folder_id' => '找不到指定的資料夾。',
            ]);
        }

        return [
            'currentFolder' => $currentFolder ? $this->transformFolder($currentFolder) : null,
            'breadcrumbs' => $this->buildBreadcrumbs($currentFolder),
            'folders' => $this->folderRepository
                ->getChildrenByParentId($user->id, $currentFolder?->id)
                ->map(fn (Folder $folder) => $this->transformFolder($folder))
                ->values(),
            'files' => $this->userFileRepository
                ->getByFolderId($user->id, $currentFolder?->id)
                ->map(fn ($file) => [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'extension' => $file->extension,
                    'mime_type' => $file->mime_type,
                    'size' => $file->size,
                    'download_url' => route('files.download', $file),
                    'created_at' => $file->created_at?->format('Y-m-d H:i:s'),
                ])
                ->values(),
        ];
    }

    private function buildBreadcrumbs(?Folder $folder): array
    {
        $items = [
            [
                'id' => null,
                'name' => '根目錄',
            ],
        ];

        if ($folder === null) {
            return $items;
        }

        $parents = [];
        $current = $folder;

        while ($current !== null) {
            array_unshift($parents, [
                'id' => $current->id,
                'name' => $current->name,
            ]);

            $current = $current->parent;
        }

        return [...$items, ...$parents];
    }

    private function transformFolder(Folder $folder): array
    {
        return [
            'id' => $folder->id,
            'name' => $folder->name,
            'path' => $folder->path,
            'created_at' => $folder->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
