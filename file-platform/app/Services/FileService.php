<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserFile;
use App\Repositories\FolderRepository;
use App\Repositories\UserFileRepository;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileService
{
    private const DISK = 'local';

    public function __construct(
        private readonly FolderRepository $folderRepository,
        private readonly UserFileRepository $userFileRepository,
        private readonly QuotaService $quotaService,
    ) {
    }

    public function upload(User $user, UploadedFile $uploadedFile, ?int $folderId): UserFile
    {
        $folder = $this->folderRepository->findUserFolderById($user->id, $folderId);

        if ($folderId !== null && $folder === null) {
            throw ValidationException::withMessages([
                'folder_id' => __('ui.messages.upload_target_not_found'),
            ]);
        }

        if ($this->userFileRepository->existsDuplicateName($user->id, $folder?->id, $uploadedFile->getClientOriginalName())) {
            throw ValidationException::withMessages([
                'file' => __('ui.messages.duplicate_file_name'),
            ]);
        }

        $this->quotaService->ensureWithinQuota($user, (int) $uploadedFile->getSize());

        $extension = $uploadedFile->getClientOriginalExtension();
        $storedName = $extension !== ''
            ? Str::uuid()->toString().'.'.$extension
            : Str::uuid()->toString();
        $directory = 'uploads/'.$user->id.'/'.now()->format('Y/m');
        $relativePath = $uploadedFile->storeAs($directory, $storedName, self::DISK);

        return DB::transaction(fn () => $this->userFileRepository->create([
            'user_id' => $user->id,
            'folder_id' => $folder?->id,
            'original_name' => $uploadedFile->getClientOriginalName(),
            'stored_name' => $storedName,
            'disk' => self::DISK,
            'path' => $relativePath,
            'mime_type' => $uploadedFile->getClientMimeType() ?: 'application/octet-stream',
            'extension' => $extension,
            'size' => $uploadedFile->getSize(),
            'hash' => hash_file('sha256', $uploadedFile->getRealPath()),
        ]));
    }

    public function download(User $user, int $fileId): StreamedResponse
    {
        $file = $this->findOwnedFile($user->id, $fileId);

        if (! Storage::disk($file->disk)->exists($file->path)) {
            throw ValidationException::withMessages([
                'file' => __('ui.messages.physical_file_missing'),
            ]);
        }

        return Storage::disk($file->disk)->download($file->path, $file->original_name);
    }

    public function delete(User $user, int $fileId): void
    {
        $file = $this->findOwnedFile($user->id, $fileId);

        DB::transaction(function () use ($file): void {
            Storage::disk($file->disk)->delete($file->path);
            $this->userFileRepository->delete($file);
        });
    }

    public function rename(User $user, int $fileId, string $name): void
    {
        $name = trim($name);
        $file = $this->findOwnedFile($user->id, $fileId);

        if ($name === '') {
            throw ValidationException::withMessages([
                'name' => __('validation.required', ['attribute' => __('ui.files.name')]),
            ]);
        }

        if ($this->userFileRepository->existsDuplicateNameExcept($user->id, $file->folder_id, $name, $file->id)) {
            throw ValidationException::withMessages([
                'name' => __('ui.messages.duplicate_file_name'),
            ]);
        }

        $file->original_name = $name;
        $this->userFileRepository->save($file);
    }

    private function findOwnedFile(int $userId, int $fileId): UserFile
    {
        $file = $this->userFileRepository->findUserFileById($userId, $fileId);

        if ($file === null) {
            throw ValidationException::withMessages([
                'file' => __('ui.messages.file_not_found'),
            ]);
        }

        return $file;
    }
}
