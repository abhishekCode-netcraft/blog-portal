<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewUser extends Model
{
    protected $fillable = [
        'google_id',
        'full_name',
        'email',
        'phone',
        'alternate_phone',
        'coupon_code',
        'affiliate_partner',
        'experience_source',
        'review_submitted_at',
        'payout_method',
        'payout_value',
        'profile_picture',
        'screenshot_paths',
        'status',
    ];

    protected $casts = [
        'screenshot_paths'    => 'array',
        'review_submitted_at' => 'datetime',
    ];
}
