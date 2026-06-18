<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'redirect_url',
        'url_type',
        'slug',
        'scan_count',
        'qr_image',
    ];
}
