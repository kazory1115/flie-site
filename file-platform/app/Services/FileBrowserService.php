<?php

namespace App\Services;

use App\Models\Folder;
use App\Models\User;
use App\Repositories\FolderRepository;
use App\Repositories\UserFileRepository;
use Illuminate\Validation\ValidationException;

class FileBrowserService
{
    private const PER_PAGE = 20;
    private const ALLOWED_SORT_BY = ['name', 'created_at', 'size'];
    private const ALLOWED_SORT_DIRECTION = ['asc', 'desc'];

    public function __construct(
        private readonly FolderRepository $folderRepository,
        private readonly UserFileRepository $userFileRepository,
    ) {
    }

    public function buildIndexData(User $user, ?int $folderId, int $page = 1, ?string $search = null, ?string $sortBy = null, ?string $sortDirection = null, array $recentUploadTargets = []): array
    {
        $search = $this->normalizeSearch($search);
        $sortBy = $this->normalizeSortBy($sortBy);
        $sortDirection = $this->normalizeSortDirection($sortDirection);
        $currentFolder = $this->folderRepository->findUserFolderById($user->id, $folderId);
        $allFolders = $this->folderRepository->getAllByUserId($user->id);

        if ($folderId !== null && $currentFolder === null) {
            throw ValidationException::withMessages([
                'folder_id' => __('ui.messages.folder_not_found'),
            ]);
        }

        $listingPage = $this->buildListingPage($user->id, $currentFolder?->id, $folderId, $page, $search, $sortBy, $sortDirection);

        return [
            'currentFolder' => $currentFolder ? $this->transformFolder($currentFolder) : null,
            'breadcrumbs' => $this->buildBreadcrumbs($currentFolder),
            'folderOptionGroups' => $this->buildFolderOptionGroups($allFolders, $recentUploadTargets),
            'folders' => $listingPage['folders']
                ->map(fn (Folder $folder) => $this->transformFolder($folder))
                ->values(),
            'files' => $listingPage['files']
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
            'pagination' => $listingPage['pagination'],
            'sort' => [
                'by' => $sortBy,
                'direction' => $sortDirection,
            ],
        ];
    }

    private function buildListingPage(int $userId, ?int $currentFolderId, ?int $requestedFolderId, int $page, ?string $search, string $sortBy, string $sortDirection): array
    {
        $folderCount = $this->folderRepository->countChildrenByParentId($userId, $currentFolderId, $search);
        $fileCount = $this->userFileRepository->countByFolderId($userId, $currentFolderId, $search);
        $total = $folderCount + $fileCount;
        $lastPage = max(1, (int) ceil($total / self::PER_PAGE));
        $currentPage = min(max(1, $page), $lastPage);
        $offset = ($currentPage - 1) * self::PER_PAGE;

        $foldersOffset = min($offset, $folderCount);
        $folderLimit = $offset < $folderCount
            ? min(self::PER_PAGE, $folderCount - $foldersOffset)
            : 0;

        $folders = $this->folderRepository->getChildrenPageSlice($userId, $currentFolderId, $foldersOffset, $folderLimit, $search, $sortBy, $sortDirection);

        $remainingSlots = self::PER_PAGE - $folders->count();
        $fileOffset = $offset >= $folderCount
            ? $offset - $folderCount
            : 0;

        $files = $this->userFileRepository->getByFolderIdPageSlice($userId, $currentFolderId, $fileOffset, $remainingSlots, $search, $sortBy, $sortDirection);

        return [
            'folders' => $folders,
            'files' => $files,
            'pagination' => $this->buildPagination($requestedFolderId, $currentPage, $lastPage, $total, $search, $sortBy, $sortDirection),
        ];
    }

    private function buildPagination(?int $folderId, int $currentPage, int $lastPage, int $total, ?string $search, string $sortBy, string $sortDirection): array
    {
        $from = $total === 0 ? 0 : (($currentPage - 1) * self::PER_PAGE) + 1;
        $to = min($currentPage * self::PER_PAGE, $total);

        return [
            'current_page' => $currentPage,
            'per_page' => self::PER_PAGE,
            'last_page' => $lastPage,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'links' => $this->buildPaginationLinks($folderId, $currentPage, $lastPage, $search, $sortBy, $sortDirection),
        ];
    }

    private function buildPaginationLinks(?int $folderId, int $currentPage, int $lastPage, ?string $search, string $sortBy, string $sortDirection): array
    {
        if ($lastPage <= 1) {
            return [];
        }

        $start = max(1, $currentPage - 2);
        $end = min($lastPage, $currentPage + 2);
        $links = [];

        $links[] = [
            'label' => __('ui.files.pagination_previous'),
            'url' => $currentPage > 1 ? $this->buildPageUrl($folderId, $currentPage - 1, $search, $sortBy, $sortDirection) : null,
            'active' => false,
        ];

        for ($page = $start; $page <= $end; $page++) {
            $links[] = [
                'label' => (string) $page,
                'url' => $this->buildPageUrl($folderId, $page, $search, $sortBy, $sortDirection),
                'active' => $page === $currentPage,
            ];
        }

        $links[] = [
            'label' => __('ui.files.pagination_next'),
            'url' => $currentPage < $lastPage ? $this->buildPageUrl($folderId, $currentPage + 1, $search, $sortBy, $sortDirection) : null,
            'active' => false,
        ];

        return $links;
    }

    private function buildPageUrl(?int $folderId, int $page, ?string $search, string $sortBy, string $sortDirection): string
    {
        $parameters = [];

        if ($folderId !== null) {
            $parameters['folder_id'] = $folderId;
        }

        if ($page > 1) {
            $parameters['page'] = $page;
        }

        if ($search !== null && $search !== '') {
            $parameters['search'] = $search;
        }

        if ($sortBy !== 'name') {
            $parameters['sort_by'] = $sortBy;
        }

        if ($sortDirection !== 'asc') {
            $parameters['sort_direction'] = $sortDirection;
        }

        return route('files.index', $parameters);
    }

    private function normalizeSearch(?string $search): ?string
    {
        $search = $search !== null ? trim($search) : null;

        return $search === '' ? null : $search;
    }

    private function normalizeSortBy(?string $sortBy): string
    {
        return in_array($sortBy, self::ALLOWED_SORT_BY, true) ? $sortBy : 'name';
    }

    private function normalizeSortDirection(?string $sortDirection): string
    {
        return in_array($sortDirection, self::ALLOWED_SORT_DIRECTION, true) ? $sortDirection : 'asc';
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
