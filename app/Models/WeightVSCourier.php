<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WeightVSCourier extends Model
{
    use HasFactory;

    protected $table = 'weight_vs_couriers';

    protected $fillable = [
        'pub_name',
        'book_type_1',
        'book_discount_1',
        'book_type_2',
        'book_discount_2',
        'book_type_3',
        'book_discount_3',
        'book_type_4',
        'book_discount_4',
        'book_type_5',
        'book_discount_5',
        'book_type_6',
        'book_discount_6',
        'location_dis',
        'link',
        'company_activity',
        'sourcing_pattern',
        'sourcing_city',
        'official_url',
        'sku_pattern',
        'marginal_gaps',
        'logo_url',
        'max_discount',
        'other_limitation',
        'complaint_frequency',
        'dealer_name',
    ];
}
