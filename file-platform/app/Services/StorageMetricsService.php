<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\FolderRepository;
use App\Repositories\UserFileRepository;

class StorageMetricsService
{
    public function __construct(
        private readonly FolderRepository $folderRepository,
        private readonly UserFileRepository $userFileRepository,
    ) {
    }

    public function getMetrics(User $user): array
    {
        $totalFiles = $this->userFileRepository->countByUserId($user->id);
        $totalFolders = $this->folderRepository->countByUserId($user->id);
        $usedBytes = $this->userFileRepository->sumSizeByUserId($user->id);

        return [
            'total_files' => $totalFiles,
            'total_folders' => $totalFolders,
            'used_bytes' => $usedBytes,
            'used_bytes_label' => $this->formatBytes($usedBytes),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $value = $bytes / 1024;
        $unitIndex = 0;

        while ($value >= 1024 && $unitIndex < count($units) - 1) {
            $value /= 1024;
            $unitIndex++;
        }

        return number_format($value, $value >= 10 ? 0 : 1).' '.$units[$unitIndex];
    }
}
