<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserFileRepository;

class DashboardService
{
    private const RECENT_UPLOAD_LIMIT = 5;

    public function __construct(
        private readonly StorageMetricsService $storageMetricsService,
        private readonly QuotaService $quotaService,
        private readonly UserFileRepository $userFileRepository,
    ) {
    }

    public function buildData(User $user): array
    {
        $metrics = $this->storageMetricsService->getMetrics($user);

        return [
            'metrics' => $metrics,
            'quota' => $this->quotaService->buildSummary($user, $metrics['used_bytes']),
            'recentUploads' => $this->userFileRepository
                ->getRecentByUserId($user->id, self::RECENT_UPLOAD_LIMIT)
                ->map(fn ($file) => [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'extension' => $file->extension,
                    'mime_type' => $file->mime_type,
                    'size' => $file->size,
                    'folder_name' => $file->folder?->name,
                    'folder_path' => $file->folder?->path,
                    'download_url' => route('files.download', $file),
                    'created_at' => $file->created_at?->format('Y-m-d H:i:s'),
                ])
                ->values(),
        ];
    }
}
