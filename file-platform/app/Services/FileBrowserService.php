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

    public function buildIndexData(User $user, ?int $folderId, array $recentUploadTargets = []): array
    {
        $currentFolder = $this->folderRepository->findUserFolderById($user->id, $folderId);
        $allFolders = $this->folderRepository->getAllByUserId($user->id);

        if ($folderId !== null && $currentFolder === null) {
            throw ValidationException::withMessages([
                'folder_id' => __('ui.messages.folder_not_found'),
            ]);
        }

        return [
            'currentFolder' => $currentFolder ? $this->transformFolder($currentFolder) : null,
            'breadcrumbs' => $this->buildBreadcrumbs($currentFolder),
            'folderOptionGroups' => $this->buildFolderOptionGroups($allFolders, $recentUploadTargets),
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
                'name' => __('ui.files.root'),
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

    private function buildFolderOptionGroups($folders, array $recentUploadTargets): array
    {
        $allOptions = collect([
            [
                'id' => null,
                'label' => __('ui.files.root'),
            ],
        ])->merge(
            $folders->map(fn (Folder $folder) => [
                'id' => $folder->id,
                'label' => str_repeat('　', substr_count($folder->path, '/')).$folder->name,
                'path' => $folder->path,
            ])
        )->values();

        $recentOptions = collect($recentUploadTargets)
            ->map(function ($target) use ($folders) {
                if ($target === 'root') {
                    return [
                        'id' => null,
                        'label' => __('ui.files.root'),
                    ];
                }

                $folder = $folders->firstWhere('id', (int) $target);

                if ($folder === null) {
                    return null;
                }

                return [
                    'id' => $folder->id,
                    'label' => str_repeat('　', substr_count($folder->path, '/')).$folder->name,
                    'path' => $folder->path,
                ];
            })
            ->filter()
            ->unique(fn ($option) => $option['id'] ?? 'root')
            ->values();

        $groups = [];

        if ($recentOptions->isNotEmpty()) {
            $groups[] = [
                'label' => __('ui.files.recent_folders'),
                'options' => $recentOptions->all(),
            ];
        }

        $groups[] = [
            'label' => __('ui.files.all_folders'),
            'options' => $allOptions->all(),
        ];

        return $groups;
    }
}
