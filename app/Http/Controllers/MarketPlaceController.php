<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\CityCost;
use App\Models\MarketplaceCommission;
use App\Models\FulfilmentType;
use App\Models\WeightVSCourier;
use App\Models\MarketPlaceCalculationSetting;

class MarketPlaceController extends Controller
{
    public function index()
    {
        $cityCosts = CityCost::all();
        $commissions = MarketplaceCommission::all();
        $fulfilmentTypes = FulfilmentType::all();
        $publications = WeightVSCourier::select('pub_name')->distinct()->get();

        return view('marketplace.calculation', compact('cityCosts', 'commissions', 'fulfilmentTypes', 'publications'));
    }

    public function getBookTypes(Request $request)
    {
        $pubName = $request->pub_name;
        $record = WeightVSCourier::where('pub_name', $pubName)->first();

        $bookTypes = [];
        if ($record) {
            for ($i = 1; $i <= 6; $i++) {
                $typeField = "book_type_$i";
                if (!empty($record->$typeField)) {
                    $bookTypes[] = [
                        'id' => $i,
                        'name' => $record->$typeField,
                        'record' => $record
                    ];
                }
            }
        }

        return response()->json($bookTypes);
    }

    public function getDiscount(Request $request)
    {
        $pubName = $request->pub_name;
        $typeId = $request->type_id;
        $record = WeightVSCourier::where('pub_name', $pubName)->first();

        $discount = 0;
        if ($record) {
            $discountField = "book_discount_$typeId";
            $discount = (float)$record->$discountField;
        }

        return response()->json(['discount' => $discount]);
    }

    public function getWeightCharges(Request $request)
    {
        $weight = (float)$request->weight;

        // Find exact match or next higher weight
        $record = MarketPlaceCalculationSetting::where('weight', '>=', $weight)
            ->orderBy('weight', 'asc')
            ->first();

        if (!$record) {
            // If no higher weight found, get the absolute maximum weight record
            $record = MarketPlaceCalculationSetting::orderBy('weight', 'desc')->first();
        }

        return response()->json([
            'courier_rate' => $record ? (float)$record->courier_rate : 0,
            'packing_charge' => $record ? (float)$record->packing_charge : 0,
        ]);
    }

    public function calculate(Request $request)
    {
        $mrp = (float)$request->mrp;
        $discountPer = (float)$request->discount;
        $transportationPer = (float)$request->transportation;
        $packagingCost = (float)$request->packaging_cost;
        $courierCharges = (float)$request->courier_charges;
        $preDefinedShipping = (float)$request->pre_defined_shipping;

        // Stage 1: Basic Costing
        // Formula: Purchase Price = MRP - (Discount % - Transportation %) of MRP
        $purchasePrice = $mrp - ($mrp * (($discountPer - $transportationPer) / 100));

        // Lookup Weight based on Purchase Price range
        $weightRecord = MarketPlaceCalculationSetting::where('min', '<=', $purchasePrice)
            ->where('max', '>=', $purchasePrice)
            ->first();
        $autoWeight = $weightRecord ? (float)$weightRecord->weight : 0;

        $netCost = $purchasePrice + $packagingCost + $courierCharges;

        // Marketplace Commission Slab Logic
        $commission = 0;
        $slab = MarketplaceCommission::where('min_range', '<=', $netCost)
            ->where(function ($query) use ($netCost) {
                $query->where('max_range', '>=', $netCost)
                    ->orWhereNull('max_range');
            })
            ->first();

        if ($slab) {
            $minC = (float)$slab->min_commission;
            $maxC = (float)$slab->max_commission;
            $minR = (float)$slab->min_range;
            $maxR = (float)$slab->max_range;

            if ($maxR > $minR && $netCost > $minR) {
                $commission = $minC + (($netCost - $minR) / ($maxR - $minR)) * ($maxC - $minC);
            } else {
                $commission = $minC;
            }
        }

        $minProfitPer = 1; // Fixed 2% profit as per requirement
        $finalCosting = $netCost + $commission + (($netCost + $commission) * ($minProfitPer / 100));
        $finalCostingRounded = ceil($finalCosting);

        // Stage 2
        $minPrice1 = $finalCostingRounded;
        $minPrice2 = $finalCostingRounded - $preDefinedShipping;

        // Competitor Logic (Passed from frontend or calculated here)
        $sellerPrice = (float)$request->seller_price;
        $sellerShipping = (float)$request->seller_shipping;
        $fulfilmentId = $request->fulfilment_id;
        $fulfilmentType = FulfilmentType::find($fulfilmentId);
        $fulfilmentDiff = $fulfilmentType ? (float)$fulfilmentType->difference_amount : 0;

        $competitorPrice = ($sellerPrice + $sellerShipping) - $fulfilmentDiff;

        $yourProductPrice = 0;
        if ($competitorPrice < $minPrice1) {
            $yourProductPrice = $minPrice2;
        } else {
            $yourProductPrice = $competitorPrice - $preDefinedShipping;
        }

        return response()->json([
            'purchase_price' => round($purchasePrice, 2),
            'net_cost' => round($netCost, 2),
            'commission' => round($commission, 2),
            'final_costing' => round($finalCosting, 2),
            'final_costing_rounded' => $finalCostingRounded,
            'auto_weight' => $autoWeight,
            'min_price_1' => $minPrice1,
            'min_price_2' => $minPrice2,
            'competitor_price' => round($competitorPrice, 2),
            'your_product_price' => round($yourProductPrice, 2),
            'your_shipping_set' => round($preDefinedShipping, 2),
        ]);
    }
}
