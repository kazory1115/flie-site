<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateFolderRequest;
use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    public function __construct(
        private readonly FolderService $folderService,
    ) {
    }

    public function store(CreateFolderRequest $request): RedirectResponse
    {
        $this->folderService->create(
            $request->user(),
            $request->string('name')->trim()->toString(),
            $request->integer('parent_id') ?: null,
        );

        return back()->with('success', '資料夾建立完成。');
    }

    public function destroy(Request $request, Folder $folder): RedirectResponse
    {
        $this->folderService->delete($request->user(), $folder->id);

        return redirect()
            ->route('files.index', ['folder_id' => $folder->parent_id])
            ->with('success', '資料夾已刪除。');
    }
}
