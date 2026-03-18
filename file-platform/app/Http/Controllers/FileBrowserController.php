<?php

namespace App\Http\Controllers;

use App\Services\FileBrowserService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FileBrowserController extends Controller
{
    public function __construct(
        private readonly FileBrowserService $fileBrowserService,
    ) {
    }

    public function index(Request $request): Response
    {
        $folderId = $request->integer('folder_id') ?: null;

        return Inertia::render('Files/Index', [
            ...$this->fileBrowserService->buildIndexData($request->user(), $folderId),
            'query' => [
                'folder_id' => $folderId,
            ],
        ]);
    }
}
