<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserFileRepository;
use Illuminate\Validation\ValidationException;

class QuotaService
{
    public const DEFAULT_QUOTA_BYTES = 1073741824;

    public function __construct(
        private readonly UserFileRepository $userFileRepository,
    ) {
    }

    public function buildSummary(User $user, ?int $usedBytes = null): array
    {
        $usedBytes ??= $this->userFileRepository->sumSizeByUserId($user->id);
        $limitBytes = $this->resolveQuotaBytes($user);
        $remainingBytes = max(0, $limitBytes - $usedBytes);
        $usagePercentage = $limitBytes > 0
            ? round(min(100, ($usedBytes / $limitBytes) * 100), 1)
            : 0.0;

        return [
            'limit_bytes' => $limitBytes,
            'limit_bytes_label' => $this->formatBytes($limitBytes),
            'used_bytes' => $usedBytes,
            'used_bytes_label' => $this->formatBytes($usedBytes),
            'remaining_bytes' => $remainingBytes,
            'remaining_bytes_label' => $this->formatBytes($remainingBytes),
            'usage_percentage' => $usagePercentage,
        ];
    }

    public function ensureWithinQuota(User $user, int $incomingBytes): void
    {
        $summary = $this->buildSummary($user);

        if (($summary['used_bytes'] + $incomingBytes) <= $summary['limit_bytes']) {
            return;
        }

        throw ValidationException::withMessages([
            'file' => __('ui.messages.quota_exceeded', [
                'limit' => $summary['limit_bytes_label'],
                'remaining' => $summary['remaining_bytes_label'],
            ]),
        ]);
    }

    private function resolveQuotaBytes(User $user): int
    {
        return (int) ($user->quota_bytes ?: self::DEFAULT_QUOTA_BYTES);
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
