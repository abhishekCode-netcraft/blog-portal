@extends('layouts.master')

@section('title', 'Complaint Details - ' . $complaint->complaint_id)

@section('content')
<div class="row mt-4 p-4">
    <div class="col-12">
        {{-- <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold mb-0">COMPLAINT DETAILS</h3>
            <a href="{{ route('admin.complaints.index') }}" class="btn btn-secondary rounded-pill px-4">
                <i class="fas fa-arrow-left me-1"></i> BACK TO DASHBOARD
            </a>
        </div> --}}

        <!-- Complaint Main Details -->
        <div class="card custom-card shadow-sm border-0 mb-4" style="border-radius: 15px;">
            <div class="card-header justify-content-between bg-white border-bottom py-3 px-4"
                style="border-radius: 15px 15px 0 0;">
                <div class="card-title fw-bold">TICKET: <span class="text-primary">{{ $complaint->complaint_id }}</span>
                </div>
                <div class="d-flex align-items-center gap-3">
                    @php
                    $badgeClass = match($complaint->status) {
                    'pending' => 'bg-warning text-dark',
                    'verification' => 'bg-info',
                    'solved' => 'bg-success',
                    'mercy' => 'bg-danger',
                    'recovered' => 'bg-secondary',
                    default => 'bg-light text-dark'
                    };
                    $statusLabel = match($complaint->status) {
                    'pending' => 'Response Needed',
                    'verification' => 'Waiting Verification',
                    'solved' => 'Solved',
                    'mercy' => 'Mercy',
                    'recovered' => 'Loss Recovered',
                    default => $complaint->status
                    };
                    @endphp
                    <span class="badge {{ $badgeClass }} px-3 py-2 fs-6">{{ $statusLabel }}</span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row mb-4">
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Created By:</p>
                        <h6 class="fw-bold">{{ $complaint->user->name }}</h6>
                        <small class="text-muted">{{ $complaint->user->email }}</small>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Created Date:</p>
                        <h6 class="fw-bold">{{ $complaint->created_at->format('d M, Y h:i A') }}</h6>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Department:</p>
                        <h6 class="fw-bold text-info">{{ $complaint->department->name }}</h6>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Issue Type:</p>
                        <h6 class="fw-bold text-info">{{ $complaint->issueType->name }}</h6>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Managed By:</p>
                        <h6 class="fw-bold text-primary text-uppercase">{{ $complaint->managed_by }}</h6>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Delivery Timeline:</p>
                        <h6 class="fw-bold text-warning">{{ $complaint->delivery_timeline }} {{
                            $complaint->delivery_timeline == 1 ? 'Day' : 'Days' }}</h6>
                    </div>
                    @if($complaint->specific_tag)
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Specific Tag:</p>
                        <h6 class="fw-bold">{{ $complaint->specific_tag ? 'YES' : 'NO' }}</h6>
                    </div>
                    <div class="col-md-3">
                        <p class="text-muted mb-1 small text-uppercase fw-bold">Email Notification:</p>
                        <h6 class="fw-bold">{{ $complaint->send_mail ? 'ENABLED' : 'DISABLED' }}</h6>
                    </div>
                    @endif

                    <div class='col-md-2'>
                        @if($complaint->attachments->count() > 0)
                            <div class="mb-4">
                            <p class="text-muted mb-1 small text-uppercase fw-bold">Attachments:</p>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach($complaint->attachments as $attachment)
                                @php
                                $extension = pathinfo($attachment->file_path, PATHINFO_EXTENSION);
                                $isImage = in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif']);

                                $fileName = explode('/', $attachment->file_path);
                                $fileName = end($fileName);
                                @endphp
                                <div class="text-center">
                                    @if($isImage)
                                    <a href="{{ route('assets', $fileName) }}" target="_blank" class="d-block mb-1">

                                        {{-- <img src="{{ asset('storage/' . $attachment->file_path) }}" alt="Attachment"
                                            class="img-thumbnail"
                                            style="width: 100px; height: 100px; object-fit: cover; border-radius: 10px;"> --}}
                                    </a>
                                    @endif
                                    <a href="{{ route('assets', $fileName) }}" target="_blank"
                                        class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fas fa-file-download me-1"></i> View Attachment
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if($complaint->specific_tag)
                <div class="alert alert-primary light mb-4 border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-1">SPECIFIC EMPLOYEE INFORMATION</h6>
                        <!-- <p class="mb-0 small">Name: <strong>{{ $complaint->employee_name }}</strong> | Email: <strong>{{
                                $complaint->employee_email }}</strong> | Mobile: <strong>{{ $complaint->employee_mobile
                                }}</strong></p>
                        @if($complaint->specific_user_email) -->
                        <p class="mb-0 small mt-1">Specific User Email: <strong>{{ $complaint->specific_user_email
                                }}</strong></p>
                        @endif
                    </div>
                </div>
                @endif

                <div class="mb-4">
                    <p class="text-muted mb-1 small text-uppercase fw-bold">Title:</p>
                    <h5 class="fw-bold">{{ $complaint->title }}</h5>
                </div>

                <div class="mb-4">
                    <p class="text-muted mb-1 small text-uppercase fw-bold">Description:</p>
                    <div class="p-3 bg-light rounded-3 border">
                        {{ $complaint->description }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Details Table -->
        <div class="card custom-card shadow-sm border-0 mb-4" style="border-radius: 15px;">
            <div class="card-header bg-white border-bottom py-3 px-4" style="border-radius: 15px 15px 0 0;">
                <div class="card-title fw-bold">ORDER DETAILS</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered text-nowrap mb-0">
                        <thead class="bg-light text-center small text-uppercase">
                            <tr>
                                <th>Order ID</th>
                                <th>Ref No</th>
                                <th>Tracking ID</th>
                                <th>Customer Name</th>
                                <th>Customer Phone</th>
                                <th>Loss Value</th>
                                <th>Self Note</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complaint->orders as $order)
                            <tr class="text-center">
                                <td class="fw-bold text-primary">{{ $order->order_id }}</td>
                                <td>{{ $order->ref_no }}</td>
                                <td>{{ $order->tracking_id }}</td>
                                <td>{{ $order->cx_name }}</td>
                                <td>{{ $order->cx_phone }}</td>
                                <td class="text-danger fw-bold">₹{{ number_format($order->loss_value, 2) }}</td>
                                <td class="text-wrap" style="max-width: 200px;">{{ $order->self_note }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Response Logs -->
        <div class="card custom-card shadow-sm border-0 mb-4" style="border-radius: 15px;">
            <div class="card-header bg-light border-bottom py-3 px-4" style="border-radius: 15px 15px 0 0;">
                <div class="card-title fw-bold">RESPONSE DETAILS / LOGS</div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered mb-0">
                        <thead class="bg-white text-center small text-uppercase">
                            <tr>
                                <th style="width: 80px;">SL. No.</th>
                                <th>Response Details</th>
                                <th>Attachment</th>
                                <th>Status</th>
                                <th>Reply Date & Time</th>
                                <th>Reply By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($complaint->replies as $index => $reply)
                            <tr>
                                <td class="text-center align-middle">{{ $index + 1 }}</td>
                                <td class="align-middle">
                                    <div class="p-2">
                                        <p class="mb-0">{{ $reply->message }}</p>
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        @foreach($reply->attachments as $att)
                                        <a href="{{ asset('storage/' . $att->file_path) }}" target="_blank"
                                            class="badge bg-info-transparent border border-info text-info py-2">
                                            <i class="fas fa-paperclip me-1"></i> View
                                        </a>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-center align-middle">
                                    @if($reply->status)
                                    @php
                                    $badgeClassLog = match($reply->status) {
                                    'pending' => 'bg-warning text-dark',
                                    'verification' => 'bg-info',
                                    'solved' => 'bg-success',
                                    'mercy' => 'bg-danger',
                                    'recovered' => 'bg-secondary',
                                    default => 'bg-light text-dark'
                                    };
                                    $statusLabelLog = match($reply->status) {
                                    'pending' => 'Response Needed',
                                    'verification' => 'Waiting Verification',
                                    'solved' => 'Solved',
                                    'mercy' => 'Mercy',
                                    'recovered' => 'Loss Recovered',
                                    default => $reply->status
                                    };
                                    @endphp
                                    <span class="badge {{ $badgeClassLog }}">{{ $statusLabelLog }}</span>
                                    @else
                                    -
                                    @endif
                                </td>
                                <td class="text-center align-middle small">
                                    <span class="fw-bold">{{ $reply->created_at->format('d M, Y') }}</span><br>
                                    <small class="text-muted">{{ $reply->created_at->format('h:i A') }}</small>
                                </td>
                                <td class="text-center align-middle text-primary fw-bold">
                                    {{ $reply->user->name }}<br>
                                    <small class="text-muted">{{ $reply->user->email }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center p-5 text-muted">
                                    <i class="fas fa-info-circle fs-30 mb-3 d-block"></i>
                                    No responses or logs found for this complaint yet.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection