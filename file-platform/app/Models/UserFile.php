<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFile extends Model
{
    use HasFactory;

    protected $table = 'files';

    protected $fillable = [
        'user_id',
        'folder_id',
        'original_name',
        'stored_name',
        'disk',
        'path',
        'mime_type',
        'extension',
        'size',
        'hash',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }
}
