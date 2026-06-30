@extends('layouts.master')

@section('title', __('Market Place Calculation'))

@section('content')
<style>
    .form-group {
        margin-bottom: 0px;
    }
</style>

<div class="card mt-5">
    <!--<div class="card-header">-->
    <!--    <h3 class="card-title">{{ __('Market Place Calculation') }}</h3>-->
    <!--</div>-->
    <div class="card-body">
        <form id="calculationForm">
            @csrf
            <!-- STAGE 1: BASIC COSTING -->
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('MRP.:') }}</label>
                        <input type="number" step="0.01" class="form-control calc-trigger" name="mrp" id="mrp" placeholder="Enter MRP">
                    </div>
                </div>
                  <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('Discount %:') }}</label>
                        <input type="number" step="0.01" class="form-control calc-trigger" name="discount" id="discount" value="0" >
                    </div>
                </div>
                <div class="col-md-3">
                     <label class="form-label">
                        <div class='w-100 d-flex justify-content-between'>
                            {{ __('Publication / Sub Category:') }}
                            
                            <div>
                                <span
                                    id='dealer_name'
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title=""
                                    style="cursor:pointer;color:blue;margin-left:5px;font-size:15px;">
                                    <i class="fa fa-industry" aria-hidden="true"></i>
                                </span>
                                
                                <span
                                    id='other_limitation'
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title=""
                                    style="cursor:pointer;color:#757505;margin-left:5px;font-size:15px;">
                                    <i class="fa fa-warning"></i>
                                </span>
        
                                <span
                                    id='complaint_frequency'
                                    data-bs-toggle="tooltip"
                                    data-bs-placement="right"
                                    title=""
                                    style="cursor:pointer;color:red;margin-left:5px;font-size:15px;">
                                    <i class="fa fa-legal"></i>
                                </span>
                            </div>
                        </div>
                    </label>

                    <div class="d-flex" style="gap:10px;">
                        <select class="form-control searchable_dropdown" name="publication" id="publication">
                            <option value="">Select Publication</option>
                            @foreach($publications as $pub)
                                <option value="{{ $pub->pub_name }}">{{ $pub->pub_name }}</option>
                            @endforeach
                        </select>

                        <select class="form-control searchable_dropdown" name="sub_category" id="sub_category">
                            <option value="">Select Sub Category</option>
                        </select>
                    </div>
                    <!--<div class="form-group">-->
                    <!--    <label class="form-label">{{ __('Publication / Sub Category: ') }}</label>-->
                    <!--      <div class="d-flex" style="grid-gap: 10px;">-->
                    <!--        <select class="form-control searchable_dropdown" name="publication" id="publication">-->
                    <!--            <option value="">Select Publication</option>-->
                    <!--            @foreach($publications as $pub)-->
                    <!--                <option value="{{ $pub->pub_name }}">{{ $pub->pub_name }}</option>-->
                    <!--            @endforeach-->
                    <!--        </select>-->
                    <!--         <select class="form-control searchable_dropdown" name="sub_category" id="sub_category">-->
                    <!--            <option value="">Select Sub Category</option>-->
                    <!--        </select>-->
                    <!--    </div>-->
                    <!--</div>-->

                </div>
            <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('Transportation Cost %:') }}</label>
                        <div class="d-flex" style="grid-gap: 10px;">
                            <input type="number" step="0.01" class="form-control calc-trigger" name="transportation" id="transportation" value="0">
                            <select class="form-control ms-2 searchable_dropdown" id="city_dropdown">
                                <option value="">Select City</option>
                                @foreach($cityCosts as $city)
                                    <option value="{{ (float)$city->cost_percentage }}">{{ $city->city_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
              
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('Purchase Price:') }}</label>
                        <input type="text" class="form-control" id="res_purchase_price" readonly>
                    </div>
                </div>

                  <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label text-red">{{ __('Product Weight (In Grams):') }}</label>
                        <input type="number" step="0.01" class="form-control" name="weight" id="weight" placeholder="Enter Weight" style="border: 1px solid red;">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label text-success">{{ __('Packaging Cost:') }}</label>
                        <input type="number" step="0.01" class="form-control calc-trigger" name="packaging_cost" id="packaging_cost" value="0" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label text-success">{{ __('Shipping Charges:') }}</label>
                        <input type="number" step="0.01" class="form-control calc-trigger" name="courier_charges" id="courier_charges" value="0" readonly>
                    </div>
                </div>
            </div>
        


            <div class="row mt-3">
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('Net Costing:') }}</label>
                        <input type="text" class="form-control" id="res_net_cost" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('Commission:') }}</label>
                        <input type="text" class="form-control" id="res_commission" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label">{{ __('Final Costing ( +1% ) | (Round-Up):') }}</label>
                        <input type="text" class="form-control" id="res_final_costing" readonly>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label class="form-label font-weight-bold text-red" style="font-weight: bold;">{{ __('Pre-Defined Shipping:') }}</label>
                        <input type="number" step="0.01" class="form-control calc-trigger" name="pre_defined_shipping" id="pre_defined_shipping" value="0" style="border: 1px solid red;">
                    </div>
                </div>
            </div>

            <hr class="my-4">

            <!--<div class="row mb-3">-->
            <!--    <div class="col-md-4">-->
            <!--        <div class="form-group">-->
            <!--            <label class="form-label font-weight-bold">{{ __('Pre-Defined Shipping:') }}</label>-->
            <!--            <input type="number" step="0.01" class="form-control calc-trigger" name="pre_defined_shipping" id="pre_defined_shipping" value="0">-->
            <!--        </div>-->
            <!--    </div>-->
            <!--</div>-->

            <div class="table-responsive mb-4">
                <table class="table table-bordered text-center">
                    <thead class="bg-warning text-dark">
                        <tr>
                            <th colspan=2 class='bg-warning text-dark' style="border-bottom: 1px solid black;">WITHOUT PRE-DEFINED SHIPPING</th>
                            <th colspan=2 class='bg-success text-white' style="border-bottom: 1px solid black;">WITH PRE-DEFINED SHIPPING</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="border-bottom: 1px solid black;"><strong>Minimum Price: </strong><span id="span_min_price_1" class="ml-2 font-weight-bold">0</span> [<span id='min_dis_1'></span>%]</td>
                            <td style="border-bottom: 1px solid black;"><strong>Maximum Price: </strong><span id="span_max_price_1" class="ml-2 font-weight-bold">0</span></td>
                            <td style="border-bottom: 1px solid black;"><strong>Minimum Price: </strong><span id="span_min_price_2" class="ml-2 font-weight-bold">0</span> [<span id='min_dis_2'></span>%]</td>
                            <td style="border-bottom: 1px solid black;"><strong>Maximum Price: </strong><span id="span_max_price_2" class="ml-2 font-weight-bold">0</span></td>
                        </tr>
                        <tr>
                            <td style="border-bottom: 1px solid black;"><strong>Limit Minimum Price: </strong><span id="limit_min_price_1" class="ml-2 font-weight-bold">0</span></td>
                            <td style="border-bottom: 1px solid black;"><strong>Limit Maximum Price: </strong><span id="limit_max_price_1" class="ml-2 font-weight-bold">0</span></td>
                            <td style="border-bottom: 1px solid black;"><strong>Limit Minimum Price: </strong><span id="limit_min_price_2" class="ml-2 font-weight-bold">0</span></td>
                            <td style="border-bottom: 1px solid black;"><strong>Limit Maximum Price: </strong><span id="limit_max_price_2" class="ml-2 font-weight-bold">0</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- STAGE 2 Header -->
            <div class="text-center bg-dark text-white p-2 mb-4">
                <h3 class="m-0">COMPETITIVE PRICING</h3>
            </div>

            <div class="bg-light p-4">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('Competitor Product Price:') }}</label>
                            <input type="number" step="0.01" class="form-control calc-trigger" name="seller_price" id="seller_price" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('Competitor Shipping:') }}</label>
                            <input type="number" step="0.01" class="form-control calc-trigger" name="seller_shipping" id="seller_shipping" value="0">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label">{{ __('Fulfilment Type:') }}</label>
                            <select class="form-control calc-trigger" name="fulfilment_id" id="fulfilment_id">
                                @foreach($fulfilmentTypes as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }} </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <div class="col-md-3">
                        <div class="form-group">
                            <label class="form-label font-weight-bold text-primary">{{ __('Competitor Price - Fullfilment:') }}</label>
                            <!--<small class="text-muted d-block mb-1">(Product Price + Shipping – Fulfilment)</small>-->
                            <input type="text" class="form-control bg-white" id="res_competitor_price" readonly>
                        </div>
                    </div>
                </div>

                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-6 text-center border-right">
                        <h5 class="text-info">{{ __('Beating Product Price:') }}</h5>
                        <div id="res_your_product_price" class="display-4 font-weight-bold text-dark">0</div>
                    </div>
                    <div class="col-md-6 text-center">
                        <h5 class="text-info">{{ __('Your Shipping Set:') }}</h5>
                        <div id="res_your_shipping_set" class="display-4 font-weight-bold text-dark">0</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('js')
<script>
var limited_dis = 0;
$(document).ready(function() {
    $(".searchable_dropdown").select2();
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let isManualWeight = false;

    // Publication change -> Load Book Types
    $('#publication').on('change', function() {
        isManualWeight = false; // Reset on change
        const pubName = $(this).val();
        $('#sub_category').html('<option value="">Select Sub Category</option>');
        $('#discount').val(0);
        
        if (pubName) {
            $.post("{{ route('marketplace.get_book_types') }}", { pub_name: pubName }, function(data) {
                $('#other_limitation').attr('data-bs-original-title', data[0].record.other_limitation);
                $('#complaint_frequency').attr('data-bs-original-title', data[0].record.complaint_frequency);
                $('#dealer_name').attr('data-bs-original-title', data[0].record.dealer_name);
                
                var mrp = Number($('#mrp').val());
                const max_dis = Number(data[0].record.max_discount);
                limited_dis = mrp-(mrp*max_dis)/100;
                
                data.forEach(function(type) {
                    $('#sub_category').append(`<option value="${type.id}">${type.name}</option>`);
                });
            });
        }
        performCalculation();
    });

    // Sub Category change -> Load Discount
    $('#sub_category').on('change', function() {
        isManualWeight = false; // Reset on change
        const typeId = $(this).val();
        const pubName = $('#publication').val();
        
        if (typeId && pubName) {
            $.post("{{ route('marketplace.get_discount') }}", { pub_name: pubName, type_id: typeId }, function(data) {
                $('#discount').val(data.discount);
                performCalculation();
            });
        } else {
            $('#discount').val(0);
            performCalculation();
        }
    });

    // Weight change -> Load Charges
    $('#weight').on('input', function(e) {
        if (e.originalEvent) { // Only if manually triggered by user input
            isManualWeight = true;
        }
        const weight = $(this).val();
        if (weight > 0) {
            $.post("{{ route('marketplace.get_weight_charges') }}", { weight: weight }, function(data) {
                $('#packaging_cost').val(data.packing_charge);
                $('#courier_charges').val(data.courier_rate);
                performCalculation();
            });
        } else {
            $('#packaging_cost').val(0);
            $('#courier_charges').val(0);
            performCalculation();
        }
    });

    // Reset manual flag on MRP or Discount change
    $('#mrp, #discount').on('input', function() {
        isManualWeight = false;
    });

    // City selection updates transportation manually but allows override
    $('#city_dropdown').on('change', function() {
        if($(this).val()) {
            $('#transportation').val($(this).val());
            performCalculation();
        }
    });

    $('.calc-trigger').on('input change', function() {
        performCalculation();
    });

    function performCalculation() {
        const formData = $('#calculationForm').serialize();
        
        $.ajax({
            url: "{{ route('marketplace.calculate') }}",
            method: "POST",
            data: formData,
            success: function(response) {
                $('#res_purchase_price').val(response.purchase_price);
                $('#res_net_cost').val(response.net_cost);
                $('#res_commission').val(response.commission);
                $('#res_final_costing').val(response.final_costing + "   |   "  + response.final_costing_rounded);

                // Auto-populate Weight if returned and NOT manual
                if (!isManualWeight && response.auto_weight > 0 && $('#weight').val() != response.auto_weight) {
                    $('#weight').val(response.auto_weight).trigger('input');
                }

                // Update Stage 2 base values
                $('#span_min_price_1').text(response.min_price_1);
                $('#span_min_price_2').text(response.min_price_2);
                $('#span_max_price_1').text($('#mrp').val() || 0);
                $('#span_max_price_2').text($('#mrp').val() || 0);

                // Update Competitor results
                $('#res_competitor_price').val(response.competitor_price);
                $('#res_your_product_price').text(response.your_product_price);
                $('#res_your_shipping_set').text(response.your_shipping_set);
                
                const mrp = Number($('#mrp').val());
                const dis = (mrp - response.min_price_1)/mrp*100;
                $("#min_dis_1").html(dis.toFixed(2));
                
                const dis_2 = (mrp - response.min_price_2)/mrp*100;
                $("#min_dis_2").html(dis_2.toFixed(2));
                
                var limit_min_price_1 = response.min_price_1 > limited_dis ? response.min_price_1 : limited_dis
                const limited_dis_1 = (mrp - limit_min_price_1)/mrp*100;
                $("#limit_min_price_1").text(limit_min_price_1 + " ["+ limited_dis_1.toFixed(2) +"]%");
                
                var limit_min_price_2 = response.min_price_2 > limited_dis ? response.min_price_2 : limited_dis
                const limited_dis_2 = (mrp - limit_min_price_2)/mrp*100;
                $("#limit_min_price_2").text(limit_min_price_2 + " ["+ limited_dis_2.toFixed(2) +"]%");
                
                $('#limit_max_price_1').text($('#mrp').val() || 0);
                $('#limit_max_price_2').text($('#mrp').val() || 0);
            }
        });
    }
});
</script>
@endpush
