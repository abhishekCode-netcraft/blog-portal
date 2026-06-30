<?php

namespace App\Http\Controllers;

use App\Models\CityCost;
use App\Models\SiteSetting;
use App\Models\Publication;
use App\Models\FieldValidation;
use App\Imports\CityCostImport;
use App\Models\WeightVSCourier;
use App\Models\GoogleCredentail;
use App\Models\BookSupplierRate;
use App\Imports\PublicationsImport;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\MarketplaceCommission;
use App\Imports\WeightVSCourierImport;
use App\Imports\BookSupplierRateImport;
use App\Models\PurchesPriceWeightCourier;
use App\Imports\MarketplaceCommissionImport;
use App\Models\MarketPlaceCalculationSetting;
use App\Imports\PurchesPriceWeightCourierImport;
use App\Imports\MarketPlaceCalculationSettingImport;

class SettingsController extends Controller
{
    /**
     * Initiate the class instance
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('role_or_permission:Settings -> Configure Blog', ['only' => ['blog']]);
        $this->middleware('role_or_permission:Settings -> Site Access', ['only' => ['site']]);
    }

    /**
     * Index function
     *
     * @return \Illuminate\View\View
     */
    public function blog()
    {
        $bloggerCreds = GoogleCredentail::where('scope', 'Blogger')->first();
        $merchantCreds = GoogleCredentail::where('scope', 'Merchant')->first();

        return view('settings.index', compact('bloggerCreds', 'merchantCreds'));
    }

    /**
     * Index function
     *
     * @return \Illuminate\View\View
     */
    public function site()
    {
        $siteSettings = SiteSetting::latest()->first();
        $cityCosts = CityCost::all();
        $commissions = MarketplaceCommission::all();

        return view('settings.site', compact('siteSettings', 'cityCosts', 'commissions'));
    }

    /**
     * Update site settings
     *
     * @return void
     */
    public function update()
    {
        $siteSettings = SiteSetting::latest()->first();

        if (request()->file('logo')) {
            $logoImage = "site/" . time() . "_logo.jpg";
            request()->file('logo')
                ->storePubliclyAs('public/' . $logoImage);
        }

        if (request()->file('homepage_image')) {
            $fileName = "site/" . time() . "_homepage_image.jpg";
            request()->file('homepage_image')
                ->storeAs("/public/" . $fileName);
        }

        if (request()->file('upload_file')) {
            $uploadFileName = "site/upload_file.xlsx";
            request()->file('upload_file')
                ->storeAs("/public/" . $uploadFileName);

            Publication::truncate();
            Excel::import(new PublicationsImport, request()->file('upload_file'));
        }

        if (request()->file('weight_file')) {
            $filePath = "site/weight_vs_courier.xlsx";
            request()->file('weight_file')->storeAs("/public/" . $filePath);
            WeightVSCourier::truncate();
            Excel::import(new WeightVSCourierImport, storage_path('app/public/' . $filePath));
        }

        if (request()->file('purches_file')) {
            $filePath = "site/purches_price_weight.xlsx";
            request()->file('purches_file')->storeAs("/public/" . $filePath);
            PurchesPriceWeightCourier::truncate();
            Excel::import(new PurchesPriceWeightCourierImport, request()->file('purches_file'));
        }

        if (request()->file('offer_item_rates')) {
            $filePath = "site/offer_item_rates.xlsx";
            request()->file('offer_item_rates')->storeAs("/public/" . $filePath);
            BookSupplierRate::truncate();
            Excel::import(new BookSupplierRateImport, request()->file('offer_item_rates'));
        }

        if (request()->file('city_cost_file')) {
            $filePath = "site/city_costs.xlsx";
            request()->file('city_cost_file')->storeAs("/public/" . $filePath);
            CityCost::truncate();
            Excel::import(new CityCostImport, request()->file('city_cost_file'));
        }

        if (request()->file('marketplace_commission_file')) {
            $filePath = "site/marketplace_commissions.xlsx";
            request()->file('marketplace_commission_file')->storeAs("/public/" . $filePath);
            MarketplaceCommission::truncate();
            Excel::import(new MarketplaceCommissionImport, request()->file('marketplace_commission_file'));
        }

        if (request()->file('marketplace_calculation_settings_file')) {
            $filePath = "site/marketplace_calculation_settings.xlsx";
            request()->file('marketplace_calculation_settings_file')->storeAs("/public/" . $filePath);
            MarketPlaceCalculationSetting::truncate();
            Excel::import(new MarketPlaceCalculationSettingImport, request()->file('marketplace_calculation_settings_file'));
        }

        if (request()->file('product_background_image')) {
            request()->file('product_background_image')
                ->storePubliclyAs('public/product_background_image.jpg');
        }

        $data = [
            'url' => request()->url,
            'logo' => $logoImage ?? $siteSettings->logo ?? '',
            'homepage_image' => $fileName ?? $siteSettings->homepage_image ?? '',
            'product_background_image' => 'custom_image.jpg',
            'watermark_text' => request()->watermark_text,
            'calc_link' => request()->calc_link,
            'button_1' => request()->button_1 . "," . request()->button_1_href,
            'button_2' => request()->button_2 . "," . request()->button_2_href,
            'button_3' => request()->button_3 . "," . request()->button_3_href,
            'button_4' => request()->button_4 . "," . request()->button_4_href,
            'listing_button_1' => request()->listing_button_1,
            'listing_button_1_link' => request()->listing_button_1_link,
            'listing_button_2' => request()->listing_button_2,
            'listing_button_2_link' => request()->listing_button_2_link,
            'upload_file' => $uploadFileName ?? $siteSettings->upload_file ?? '',
        ];

        if (!$siteSettings) SiteSetting::create($data);

        if ($siteSettings) $siteSettings->update($data);

        session()->flash('success', 'Settings updated successfully');

        return redirect()->back();
    }

    /**
     * Fields Validation View
     */
    public function fieldsValidations()
    {
        $notAllowedNames = FieldValidation::where('name', "!=", '')
            ->where('status', 1)
            ->get();

        $notAllowedlinks = FieldValidation::where('links', "!=", '')
            ->where('status', 1)
            ->get();

        return view('settings.fields-validations', compact('notAllowedNames', 'notAllowedlinks'));
    }

    /**
     * Save Keywords and Links 
     */
    public function keywordsNotAllowed()
    {
        if (request()->name) {
            FieldValidation::create([
                'name' => request()->name,
                'links' => null,
                'status' => 1
            ]);
        } else if (request()->link) {
            FieldValidation::create([
                'name' => null,
                'links' => request()->link,
                'allowed' => request()->allow ? 1 : 0,
                'status' => 1
            ]);
        }

        session()->flash('success', 'Updated successfully');

        return redirect()->back();
    }

    /**
     * Update Keywords 
     */
    public function updateKeywords($id)
    {
        $row = FieldValidation::find($id);

        $row->update([
            'name' => request()->name ?? '',
            'links' => request()->link,
        ]);

        session()->flash('success', 'Updated successfully');

        return redirect()->back();
    }

    /**
     * Delete Keywords and Links 
     */
    public function keywordsDelete($id)
    {
        FieldValidation::find($id)->delete();

        session()->flash('success', 'Deleted successfully');

        return redirect()->back();
    }

    /**
     * Validate Fields
     */
    public function fieldsValidate()
    {
        return FieldValidation::select('name', 'links', 'allowed')->get();
    }

    public function termNcondition()
    {
        $siteSettings = SiteSetting::find(1);

        return view('settings.term-policies', compact('siteSettings'));
    }

    public function saveAndUpdateTermNcondition()
    {
        $siteSettings = SiteSetting::find(1);

        $siteSettings->update(request()->all());

        return redirect()->back();
    }

    public function getTermCondition()
    {
        $siteSettings = SiteSetting::find(1);

        return view('settings.term-condition', compact('siteSettings'));
    }

    public function getPolicies()
    {
        $siteSettings = SiteSetting::find(1);

        return view('settings.policy', compact('siteSettings'));
    }

    public function downloadCityCostSample()
    {
        $headers = ['city_name', 'cost_percentage'];
        $data = [
            ['Delhi', '2%'],
            ['Mumbai', '1%'],
        ];

        return response()->streamDownload(function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 'sample_city_costs.csv');
    }

    public function downloadMarketplaceCommissionSample()
    {
        $headers = ['min_range', 'max_range', 'min_commission', 'max_commission'];
        $data = [
            ['0', '290', '8', '10'],
            ['291', '470', '25', '30'],
        ];

        return response()->streamDownload(function () use ($headers, $data) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $headers);
            foreach ($data as $row) {
                fputcsv($file, $row);
            }
            fclose($file);
        }, 'sample_marketplace_commissions.csv');
    }
}
