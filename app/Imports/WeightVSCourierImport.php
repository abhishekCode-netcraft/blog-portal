<?php
namespace App\Imports;

use App\Models\WeightVSCourier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class WeightVSCourierImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return new WeightVSCourier([
            'pub_name' => $row['pub_name'],
            'book_type_1' => $row['book_type_1'],
            'book_discount_1' => $row['book_discount_1'],
            'book_type_2' => $row['book_type_2'],
            'book_discount_2' => $row['book_discount_2'],
            'book_type_3' => $row['book_type_3'],
            'book_discount_3' => $row['book_discount_3'],
            'book_type_4' => $row['book_type_4'],
            'book_discount_4' => $row['book_discount_4'],
            'book_type_5' => $row['book_type_5'],
            'book_discount_5' => $row['book_discount_5'],
            'book_type_6' => $row['book_type_6'],
            'book_discount_6' => $row['book_discount_6'],
            'location_dis' => $row['location_dis'],
            'company_activity' => $row['company_activity'],
            'sourcing_pattern' => $row['sourcing_pattern'],
            'sourcing_city' => $row['sourcing_city'],
            'official_url' => $row['official_url'],
            'sku_pattern' => $row['sku_pattern'],
            'marginal_gaps' => $row['marginal_gaps'],
            'logo_url' => $row['logo_url'] ?? null,
            'max_discount' => $row['max_discount'] ?? null,
            'other_limitation' => $row['other_limitation'] ?? null,
            'complaint_frequency' => $row['complaint_frequency'] ?? null,
            'dealer_name' => $row['dealer_name'] ?? null,
        ]);
    }
}

