@extends('layouts.master')

@section('title', 'Create Official Complaints')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card overflow-hidden shadow-sm">
            <!-- Header -->
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0">Create New Task</h5>
                    <small>Raise your issue with detailed order information</small>
                </div>
                <span class="badge bg-light text-dark" style="font-size: 18px;">Task ID: AUTO-GENERATED</span>
            </div>

            <!-- Top Info -->
            <div class="p-3 border-bottom d-flex justify-content-between flex-wrap">
                <div>
                    <strong>Created Date:</strong> {{ \Carbon\Carbon::now()->format('d M, Y') }}
                </div>
                <div><strong>Created By:</strong> {{ auth()->user()->name }}</div>
                
                <div><strong>User's Email:</strong> {{ auth()->user()->email }}</div>
            </div>

            <!-- Body -->
            <div class="card-body" style='background-color: #ffefcf;'>
                <form action="{{ route('admin.official-complaints.store') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <!-- Left -->
                        <div class="col-md-4">
                            <label class="form-label">Task - Issue Type *</label>
                            <select class="form-control" name="issue_type_id">

                                <option>Select Issue Type</option>
                                @foreach ($issueTypes as $issueType)
                                <option value="{{ $issueType->id }}">{{ $issueType->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Task - Department *</label>
                            <select class="form-control" name="department_id">
                                <option>Select Department</option>
                                @foreach ($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Task - Delivery Timeline: </label>
                            <select name="delivery_timeline" class="form-control">
                                @for($i = 1; $i <= 7; $i++) <option value="{{ $i }}" {{ $i==3 ? 'selected' : '' }}
                                    class="bg-white text-dark">{{ $i }} {{ $i == 1 ? 'Day' : 'Days' }}</option>
                                    @endfor
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Task Title ( In Short ) *</label>
                            <input type="text" class="form-control" placeholder="Brief subject/title" name="title">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">File Attach - Multiple Attach ( PDF, Word, XLS, JPG, PNG )</label>
                            <input type="file" class="form-control" multiple name="files[]">
                            {{-- <small class="text-muted">Image + PDF + Excel allowed</small> --}}
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Detailed Description *</label>
                            <textarea class="form-control" rows="3" placeholder="Describe your issue in detail..."
                                name="description"></textarea>
                        </div>
                    </div>

                    <div class='row'>
                        <div class="col-3 mt-3">
                            <label class="form-label">Specific User Tag Via Email *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input specific_tag" type="radio" name="specific_tag" value=1>
                                <label class="form-check-label">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input specific_tag" type="radio" name="specific_tag" value=0
                                    checked>
                                <label class="form-check-label">No</label>
                            </div>
                        </div>

                        <div class='col-3 mt-3 d-none specific_user_email'>
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" placeholder="Enter email address"
                                name="specific_user_email">
                        </div>

                        <div class="col-3 mt-3">
                            <label class="form-label">Email Notification to Specific User? *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="send_mail" id="official_mail_yes"
                                    value=1>
                                <label class="form-check-label" for="official_mail_yes">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="send_mail" id="official_mail_no"
                                    value=0 checked>
                                <label class="form-check-label" for="official_mail_no">No</label>
                            </div>
                        </div>

                        <div class="col-3 mt-3">
                            <label class="form-label">Complaint Verification By *</label>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="managed_by" value='self with admin'
                                    checked>
                                <label class="form-check-label">Self + Admin</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="managed_by" value='admin'>
                                <label class="form-check-label">Admin</label>
                            </div>
                        </div>
                    </div>

                    <!-- Order Details -->
                    <div class="mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <h6>ORDER DETAILS</h6>
                            <button type='button' class="btn btn-dark btn-sm" id='addOrderBtn'>+ Add Order</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered text-center">
                                <thead class="table-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Ref No.</th>
                                        <th>Tracking ID</th>
                                        <th>Customer Name</th>
                                        <th>Customer Phone</th>
                                        <th>Loss/Order Value</th>
                                        <th>Self Note</th>
                                    </tr>
                                </thead>
                                <tbody id="orderTableBody">
                                    <tr>
                                        <td>
                                            <input type="text" name="orders[1][order_id]" class="form-control"
                                                placeholder="Order ID">
                                        </td>
                                        <td>
                                            <input type="text" name="orders[1][ref_no]" class="form-control"
                                                placeholder="Ref No">
                                        </td>
                                        <td>
                                            <input type="text" name="orders[1][tracking_id]" class="form-control"
                                                placeholder="Tracking ID">
                                        </td>
                                        <td>
                                            <input type="text" name="orders[1][cx_name]" class="form-control"
                                                placeholder="Customer Name">
                                        </td>
                                        <td>
                                            <input type="text" name="orders[1][cx_phone]" class="form-control"
                                                placeholder="Phone">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="orders[1][loss_value]"
                                                class="form-control" placeholder="0.00">
                                        </td>
                                        <td>
                                            <textarea name="orders[1][self_note]" class="form-control"
                                                placeholder="Any notes..."></textarea>
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Footer Buttons -->
                    <div class="d-flex justify-content-end mt-4">
                        <a href="{{ route('admin.official-complaints.index') }}"
                            class="btn btn-outline-secondary me-2">Back</a>
                        <button class="btn btn-primary">Submit Task</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


@endsection

@push('js')
<script>
    $(document).ready(function () {
        let rowCount = 1;

        // Add Order Row
        $('#addOrderBtn').on('click', function () {

            rowCount++;

            let row = `
                    <tr>
                        <td>
                            <input type="text" name="orders[${rowCount}][order_id]" class="form-control" placeholder="Order ID">
                        </td>
                        <td>
                            <input type="text" name="orders[${rowCount}][ref_no]" class="form-control" placeholder="Ref No">
                        </td>
                        <td>
                            <input type="text" name="orders[${rowCount}][tracking_id]" class="form-control" placeholder="Tracking ID">
                        </td>
                        <td>
                            <input type="text" name="orders[${rowCount}][customer_name]" class="form-control" placeholder="Customer Name">
                        </td>
                        <td>
                            <input type="text" name="orders[${rowCount}][customer_phone]" class="form-control" placeholder="Phone">
                        </td>
                        <td>
                            <input type="number" step="0.01" name="orders[${rowCount}][loss_value]" class="form-control" placeholder="0.00">
                        </td>
                        <td>
                            <textarea name="orders[${rowCount}][self_note]" class="form-control" placeholder="Any notes..."></textarea>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm removeRow">X</button>
                        </td>
                    </tr>
                `;

            $('#orderTableBody').append(row);
        });

        // Remove Row
        $(document).on('click', '.removeRow', function () {
            $(this).closest('tr').remove();
        });

        $('.specific_tag').on('change', function () {
            if ($(this).val() == '1') {
                $('.specific_user_email').removeClass('d-none');
                $('#official_mail_yes').prop('checked', true);
            } else {
                $('.specific_user_email').addClass('d-none');
                $('#official_mail_no').prop('checked', true);
            }
        });
    });
</script>
@endpush