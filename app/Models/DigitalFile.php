<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class DigitalFile extends Model
{
    public const SOURCE_UPLOAD = 'upload';
    public const SOURCE_EXTERNAL_URL = 'external_url';

    protected $fillable = [
        'product_id',
        'filename',
        'filepath',
        'disk',
        'filesize',
        'filetype',
        'source_type',
        'external_url',
    ];

    protected $casts = [
        'filesize' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isExternalUrl(): bool
    {
        return ($this->source_type === self::SOURCE_EXTERNAL_URL)
            || (!empty($this->external_url) && empty($this->filepath));
    }

    public function resolvedDisk(): string
    {
        return (string) ($this->disk ?: 'local');
    }

    public function deleteStoredAsset(): void
    {
        if ($this->isExternalUrl()) {
            return;
        }

        $path = (string) ($this->filepath ?? '');
        if ($path === '') {
            return;
        }

        Storage::disk($this->resolvedDisk())->delete($path);
    }
}
