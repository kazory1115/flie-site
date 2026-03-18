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
        $folderId = $request->integer('folder_id') ?: null;

        $this->fileService->upload(
            $request->user(),
            $request->file('file'),
            $folderId,
        );

        $recentTargets = $request->session()->get('recent_upload_targets', []);
        $recentTargets = array_values(array_filter($recentTargets, fn ($target) => $target !== ($folderId ?? 'root')));
        array_unshift($recentTargets, $folderId ?? 'root');
        $request->session()->put('recent_upload_targets', array_slice($recentTargets, 0, 5));

        return back()->with('success', __('ui.messages.file_uploaded'));
    }

    public function download(Request $request, UserFile $file): StreamedResponse
    {
        return $this->fileService->download($request->user(), $file->id);
    }

    public function destroy(Request $request, UserFile $file): RedirectResponse
    {
        $this->fileService->delete($request->user(), $file->id);

        return back()->with('success', __('ui.messages.file_deleted'));
    }
}
