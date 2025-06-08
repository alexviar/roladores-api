<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Filesystem\LocalFilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class Rolador extends Model
{
    /** @use HasFactory<\Database\Factories\RoladorFactory> */
    use HasFactory;
}
