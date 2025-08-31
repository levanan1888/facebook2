<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'site_name',
        'logo_path',
    ];

    public static function current(): ?self
    {
        return static::query()->latest('id')->first();
    }

    public function getSiteName(): string
    {
        return $this->site_name ?: config('app.name');
    }

    public function getLogoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
