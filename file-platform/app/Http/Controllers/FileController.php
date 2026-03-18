<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadFileRequest;
use App\Models\UserFile;
use App\Services\FileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileController extends Controller
{
    public function __construct(
        private readonly FileService $fileService,
    ) {
    }

    public function store(UploadFileRequest $request): RedirectResponse
    {
        $this->fileService->upload(
            $request->user(),
            $request->file('file'),
            $request->integer('folder_id') ?: null,
        );

        return back()->with('success', '檔案上傳完成。');
    }

    public function download(Request $request, UserFile $file): StreamedResponse
    {
        return $this->fileService->download($request->user(), $file->id);
    }

    public function destroy(Request $request, UserFile $file): RedirectResponse
    {
        $this->fileService->delete($request->user(), $file->id);

        return back()->with('success', '檔案已刪除。');
    }
}
