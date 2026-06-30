@extends('layouts.master')

@section('title', __('Review'))

@section('content')

<style>
    .table th, .table td {
        white-space: nowrap;
    }
</style>

<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="mb-0">{{ __('Reviews') }}</h4>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('review.index') }}" class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search Name, Email, Phone, Coupon..."
                        value="{{ request('search') }}">
                </div>
                <div class="col-md-2"> <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}> Pending </option>
                        <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}> Approved </option>
                        <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}> Rejected </option>
                    </select> </div>
                <div class="col-md-2"> <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2"> <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-2 d-flex gap-2"> <button type="submit" class="btn btn-primary w-100"> Filter </button> <a
                        href="{{ route('review.index') }}" class="btn btn-secondary w-100"> Reset </a> </div>
            </form>
            @if($reviewUsers->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Created At</th>
                                <th>Current Status</th>
                                <th>Profile</th>
                                <th>Full Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Alternate Phone</th>
                                <th>Coupon Code</th>
                                <th>Affiliate Partner</th>
                                <th>Rated At</th>
                                <th>Review Submitted At</th>
                                <th>Payout Type</th>
                                <th>Payout Method</th>
                                <th>Total Attached Screenshots</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($reviewUsers as $reviewUser)
                                <tr>
                                    <td>{{ $loop->iteration + (($reviewUsers->currentPage() - 1) * $reviewUsers->perPage()) }}</td>
                                    
                                    <td>{{ $reviewUser->created_at->format('d M Y h:i A') }}</td>
                                    
                                    <td>
                                        @if($reviewUser->status == 'approved')
                                            <span class="badge bg-success">
                                                Approved / Granted
                                            </span>
                                        @elseif($reviewUser->status == 'rejected')
                                            <span class="badge bg-danger">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                Pending
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if($reviewUser->profile_picture)
                                            <img
                                                src="{{ $reviewUser->profile_picture }}"
                                                width="60"
                                                height="60"
                                                class="rounded-circle"
                                                style="object-fit: cover;"
                                            >
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>{{ $reviewUser->full_name }}</td>

                                    <td>{{ $reviewUser->email }}</td>

                                    <td>{{ $reviewUser->phone }}</td>

                                    <td>{{ $reviewUser->alternate_phone ?? '-' }}</td>

                                    <td>{{ $reviewUser->coupon_code ?? '-' }}</td>

                                    <td>
                                        <span class="badge {{ $reviewUser->affiliate_partner == 'Yes' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $reviewUser->affiliate_partner }}
                                        </span>
                                    </td>

                                    <td>{{ ucfirst($reviewUser->experience_source) }}</td>

                                    <td>
                                        {{ optional($reviewUser->review_submitted_at)->format('d M Y h:i A') }}
                                    </td>

                                    <td>{{ $reviewUser->payout_method }}</td>

                                    <td>{{ $reviewUser->payout_value }}</td>

                                    <td>
                                        @if($reviewUser->screenshot_paths)
                                            @foreach($reviewUser->screenshot_paths as $image)
                                            @php
                                                $imageName = @explode('/', $image)[1] ?? '';
                                            @endphp
                                                <a
                                                    href="{{ route('assets', $imageName) }}"
                                                    target="_blank"
                                                    class="d-inline-block mb-1"
                                                >
                                                    <img
                                                        src="{{ route('assets', $imageName) }}"
                                                        width="50"
                                                        height="50"
                                                        class=""
                                                        style="object-fit: cover;"
                                                    >
                                                </a>
                                            @endforeach
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                    
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-primary open-status-modal"
                                                data-id="{{ $reviewUser->id }}"
                                                data-status="{{ $reviewUser->status }}"
                                                data-remarks="{{ $reviewUser->remarks }}"
                                            >
                                                Status
                                            </button>
                                    
                                            <form action="{{ route('review.destroy', $reviewUser->id) }}"
                                                method="POST"
                                                class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
    
                                                <button onclick="return confirm('Are you sure?')" type="submit"
                                                        class="btn btn-danger btn-sm">
                                                    Delete
                                                </button>
                                            </form>
                                    
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                {{ $reviewUsers->links() }}
            @else
                <div class="text-center py-4">
                    <p class="mb-0">No reviews found.</p>
                </div>
            @endif
        </div>
    </div>
</div>

<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Update Review Status
                </h5>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body">

                <input type="hidden" id="review_id">

                <div class="mb-3">
                    <label class="form-label">
                        Status
                    </label>

                    <select class="form-select" id="modal_status">
                        <option value="pending">
                            Pending
                        </option>

                        <option value="approved">
                            Approved / Granted
                        </option>

                        <option value="rejected">
                            Rejected
                        </option>
                    </select>
                </div>

                {{-- <div class="mb-3">
                    <label class="form-label">
                        Remarks
                    </label>

                    <textarea
                        class="form-control"
                        id="modal_remarks"
                        rows="4"
                    ></textarea>
                </div> --}}

            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn btn-secondary"
                    data-bs-dismiss="modal">
                    Cancel
                </button>

                <button
                    type="button"
                    class="btn btn-primary"
                    id="saveStatusBtn">
                    Save Changes
                </button>
            </div>

        </div>
    </div>
</div>

@endsection

@push('js')
<script>
// $(document).on('submit', '.delete-form', function (e) {
//     if (!confirm('Are you sure you want to delete this review?')) {
//         e.preventDefault();
//     }
// });

$(document).on('click', '.open-status-modal', function () {
    $('#review_id').val($(this).data('id'));

    $('#modal_status').val($(this).data('status'));

    $('#modal_remarks').val($(this).data('remarks'));

    $('#statusModal').modal('show');
});

$('#saveStatusBtn').on('click', function () {
    let id = $('#review_id').val();

    $.ajax({
        url: "{{ url('admin/review') }}/" + id + "/status",
        type: "POST",
        data: {
            _token: "{{ csrf_token() }}",
            status: $('#modal_status').val(),
            remarks: $('#modal_remarks').val()
        },

        success: function (response) {

            $('#statusModal').modal('hide');

            alert(response.message);

            location.reload();
        },

        error: function (xhr) {

            alert(
                xhr.responseJSON?.message ||
                'Failed to update status.'
            );
        }
    });
});
</script>


@endpush