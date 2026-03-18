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
    ) {
    }

    public function upload(User $user, UploadedFile $uploadedFile, ?int $folderId): UserFile
    {
        $folder = $this->folderRepository->findUserFolderById($user->id, $folderId);

        if ($folderId !== null && $folder === null) {
            throw ValidationException::withMessages([
                'folder_id' => '上傳目標資料夾不存在。',
            ]);
        }

        if ($this->userFileRepository->existsDuplicateName($user->id, $folder?->id, $uploadedFile->getClientOriginalName())) {
            throw ValidationException::withMessages([
                'file' => '同一資料夾內已存在相同檔名的檔案。',
            ]);
        }

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
                'file' => '實體檔案不存在，請重新上傳。',
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

    private function findOwnedFile(int $userId, int $fileId): UserFile
    {
        $file = $this->userFileRepository->findUserFileById($userId, $fileId);

        if ($file === null) {
            throw ValidationException::withMessages([
                'file' => '找不到指定檔案。',
            ]);
        }

        return $file;
    }
}
