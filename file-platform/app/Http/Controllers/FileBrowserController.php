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
        $page = max(1, $request->integer('page', 1));
        $search = $request->string('search')->trim()->value() ?: null;
        $sortBy = $request->string('sort_by')->trim()->value() ?: null;
        $sortDirection = $request->string('sort_direction')->trim()->value() ?: null;

        return Inertia::render('Files/Index', [
            ...$this->fileBrowserService->buildIndexData(
                $request->user(),
                $folderId,
                $page,
                $search,
                $sortBy,
                $sortDirection,
                $request->session()->get('recent_upload_targets', []),
            ),
            'query' => [
                'folder_id' => $folderId,
                'page' => $page,
                'search' => $search,
                'sort_by' => $sortBy,
                'sort_direction' => $sortDirection,
            ],
        ]);
    }
}
