<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use App\Models\ComplaintUser;
use App\Models\ComplaintReply;
use App\Models\Department;
use App\Models\IssueType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\ComplaintRaisedMail;
use App\Mail\ComplaintReplyMail;
use App\Mail\ComplaintSolvedMail;

class AdminComplaintController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = Complaint::whereNotNull('user_id')
            ->where('type', 'task')
            ->with(['user', 'issueType', 'department', 'complaint_user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('complaint_id', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $counts = [
            'pending' => Complaint::whereNotNull('user_id')->where('type', 'task')->where('status', 'pending')->count(),
            'verification' => Complaint::whereNotNull('user_id')->where('type', 'task')->where('status', 'verification')->count(),
            'solved' => Complaint::whereNotNull('user_id')->where('type', 'task')->where('status', 'solved')->count(),
            'mercy' => Complaint::whereNotNull('user_id')->where('type', 'task')->where('status', 'mercy')->count(),
            'recovered' => Complaint::whereNotNull('user_id')->where('type', 'task')->where('status', 'recovered')->count(),
            'all' => Complaint::whereNotNull('user_id')->where('type', 'task')->count(),
        ];

        $users = User::orderBy('name')->get();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $complaints = $query->latest()->get();

        return view('admin.complaints.index', compact('complaints', 'counts', 'status', 'users'));
    }

    public function create()
    {
        $issueTypes = IssueType::where('status', 1)
            ->get();

        $departments = Department::where('status', 1)
            ->get();

        return view('admin.complaints.create', compact('issueTypes', 'departments'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'issue_type_id' => 'required|exists:issue_types,id',
                'department_id' => 'required|exists:departments,id',
                'title' => 'required|string|max:1000',
                'description' => 'required|string',
                'specific_tag' => 'required|boolean',
                'send_mail' => 'required|boolean',
                'orders' => 'nullable|array',
                'orders.*.order_id' => 'nullable|string',
                'orders.*.ref_no' => 'nullable|string',
                'orders.*.tracking_id' => 'nullable|string',
                'orders.*.cx_name' => 'nullable|string',
                'orders.*.cx_phone' => 'nullable|string',
                'orders.*.loss_value' => 'nullable|numeric',
                'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,xls|max:5120',
                'delivery_timeline' => 'required|integer|min:1|max:7',
                'managed_by' => 'required|string|in:self with admin,admin',
                'specific_user_email' => 'required_if:specific_tag,1|nullable|email',
            ]);

            $today = date('dmY');
            $count = Complaint::whereDate('created_at', now()->toDateString())->count() + 1;
            $complaint_id = 'charge-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $complaint = Complaint::create([
                'complaint_id' => $complaint_id,
                'user_id' => auth()->user()->id,
                'issue_type_id' => $request->issue_type_id,
                'department_id' => $request->department_id,
                'title' => $request->title,
                'description' => $request->description,
                'delivery_timeline' => $request->delivery_timeline . ($request->delivery_timeline == 1 ? ' Day' : ' Days'),
                'managed_by' => $request->managed_by,
                'specific_tag' => $request->specific_tag,
                'employee_name' => $request->employee_name,
                'employee_email' => $request->employee_email,
                'employee_mobile' => $request->employee_mobile,
                'send_mail' => $request->send_mail,
                'specific_user_email' => $request->specific_user_email,
                'status' => 'pending'
            ]);

            // Initial creation log entry
            ComplaintReply::create([
                'complaint_id' => $complaint->id,
                'user_id' => Auth::id(),
                'message' => 'Complaint Created By ' . Auth::user()->name,
                'status' => 'pending'
            ]);

            if ($request->has('orders')) {
                foreach ($request->orders as $orderData) {
                    $hasData = array_filter($orderData, fn($value) => !is_null($value) && $value !== '');

                    if (!empty($hasData)) {
                        $complaint->orders()->create($orderData);
                    }
                }
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('complaints/attachments', 'public');
                    $complaint->attachments()->create(['file_path' => $path]);
                }
            }

            // Email Notification for Complaint Raised
            $adminEmails = User::role('Super Admin')->pluck('email')->toArray();
            $deptHeadEmail = $complaint->department->email;
            if ($adminEmails || $deptHeadEmail) {
                Mail::to($adminEmails)->cc($deptHeadEmail)->send(new ComplaintRaisedMail($complaint));
            }

            return redirect()->route('admin.complaints.index')->with('success', 'Complaint submitted successfully with ID: ' . $complaint_id);
        }
        catch (\Exception $e) {
            return back()->with('error', 'An error occurred while submitting the complaint: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $complaint = Complaint::with(['user', 'issueType', 'department', 'orders', 'attachments', 'replies.user', 'replies.attachments', 'complaint_user'])
            ->findOrFail($id);

        return view('admin.complaints.show', compact('complaint'));
    }

    public function storeReply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required|string',
            'status' => 'required|string',
            'attachments.*' => 'nullable|file|max:5120'
        ]);

        $complaint = Complaint::findOrFail($id);

        $reply = ComplaintReply::create([
            'complaint_id' => $complaint->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'status' => $request->status
        ]);

        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('complaints/replies', 'public');
                $reply->attachments()->create(['file_path' => $path]);
            }
        }

        $complaint->update(['status' => $request->status]);

        // Email Notification
        $adminEmails = User::role('Super Admin')->pluck('email')->toArray();
        $deptHeadEmail = $complaint->department->email;
        $creatorEmail = $complaint->complaint_user ? $complaint->complaint_user->email : ($complaint->user ? $complaint->user->email : null);

        if (in_array($request->status, ['solved', 'mercy', 'recovered'])) {
            $statusLabels = [
                'solved' => 'Solved',
                'mercy' => 'Mercy',
                'recovered' => 'Loss Recovered'
            ];
            Mail::to($adminEmails)->cc($deptHeadEmail)->send(new ComplaintSolvedMail($complaint, $statusLabels[$request->status]));
        }
        else {
            if ($creatorEmail) {
                Mail::to($adminEmails)->cc($creatorEmail)->send(new ComplaintReplyMail($complaint, $reply));
            }
            else {
                Mail::to($adminEmails)->send(new ComplaintReplyMail($complaint, $reply));
            }
        }

        return back()->with('success', 'Reply submitted and status updated.');
    }

    public function officialIndex(Request $request)
    {
        $status = $request->get('status', 'pending');

        $query = Complaint::whereNotNull('user_id')
            ->where('type', 'official')
            ->with(['user', 'issueType', 'department', 'complaint_user']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('complaint_id', 'LIKE', "%{$search}%")
                    ->orWhere('title', 'LIKE', "%{$search}%")
                    ->orWhere('description', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $counts = [
            'pending' => Complaint::whereNotNull('user_id')->where('type', 'official')->where('status', 'pending')->count(),
            'verification' => Complaint::whereNotNull('user_id')->where('type', 'official')->where('status', 'verification')->count(),
            'solved' => Complaint::whereNotNull('user_id')->where('type', 'official')->where('status', 'solved')->count(),
            'mercy' => Complaint::whereNotNull('user_id')->where('type', 'official')->where('status', 'mercy')->count(),
            'recovered' => Complaint::whereNotNull('user_id')->where('type', 'official')->where('status', 'recovered')->count(),
            'all' => Complaint::whereNotNull('user_id')->where('type', 'official')->count(),
        ];

        $users = User::orderBy('name')->get();

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $complaints = $query->latest()->get();

        return view('admin.official_complaints.index', compact('complaints', 'counts', 'status', 'users'));
    }

    public function officialCreate()
    {
        $issueTypes = IssueType::where('status', 1)
            ->where('type', 'official')
            ->get();

        $departments = Department::where('status', 1)
            ->where('type', 'official')
            ->get();

        return view('admin.official_complaints.create', compact('issueTypes', 'departments'));
    }

    public function officialStore(Request $request)
    {
        try {
            $request->validate([
                'issue_type_id' => 'required|exists:issue_types,id',
                'department_id' => 'required|exists:departments,id',
                'title' => 'required|string|max:1000',
                'description' => 'required|string',
                'specific_tag' => 'required|boolean',
                'send_mail' => 'required|boolean',
                'orders' => 'nullable|array',
                'orders.*.order_id' => 'nullable|string',
                'orders.*.ref_no' => 'nullable|string',
                'orders.*.tracking_id' => 'nullable|string',
                'orders.*.cx_name' => 'nullable|string',
                'orders.*.cx_phone' => 'nullable|string',
                'orders.*.loss_value' => 'nullable|numeric',
                'files.*' => 'nullable|file|mimes:jpg,jpeg,png,pdf,xlsx,xls|max:5120',
                'delivery_timeline' => 'required|integer|min:1|max:7',
                'managed_by' => 'required|string|in:self with admin,admin',
                'specific_user_email' => 'required_if:specific_tag,1|nullable|email',
            ]);

            $today = date('dmY');
            $count = Complaint::whereDate('created_at', now()->toDateString())->where('type', 'official')->count() + 1;
            $complaint_id = 'Tm-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

            $complaint = Complaint::create([
                'complaint_id' => $complaint_id,
                'user_id' => auth()->user()->id,
                'issue_type_id' => $request->issue_type_id,
                'department_id' => $request->department_id,
                'title' => $request->title,
                'description' => $request->description,
                'delivery_timeline' => $request->delivery_timeline . ($request->delivery_timeline == 1 ? ' Day' : ' Days'),
                'managed_by' => $request->managed_by,
                'specific_tag' => $request->specific_tag,
                'employee_name' => $request->employee_name,
                'employee_email' => $request->employee_email,
                'employee_mobile' => $request->employee_mobile,
                'send_mail' => $request->send_mail,
                'specific_user_email' => $request->specific_user_email,
                'status' => 'pending',
                'type' => 'official'
            ]);

            // Initial creation log entry
            ComplaintReply::create([
                'complaint_id' => $complaint->id,
                'user_id' => Auth::id(),
                'message' => 'Official Complaint Created By ' . Auth::user()->name,
                'status' => 'pending'
            ]);

            if ($request->has('orders')) {
                foreach ($request->orders as $orderData) {
                    $hasData = array_filter($orderData, fn($value) => !is_null($value) && $value !== '');

                    if (!empty($hasData)) {
                        $complaint->orders()->create($orderData);
                    }
                }
            }

            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $path = $file->store('complaints/files', 'public');
                    $complaint->attachments()->create(['file_path' => $path]);
                }
            }

            // Email Notification for Complaint Raised
            $adminEmails = User::role('Super Admin')->pluck('email')->toArray();
            $deptHeadEmail = $complaint->department->email;

            if ($adminEmails || $deptHeadEmail) {
                Mail::to($adminEmails)->cc($deptHeadEmail)->send(new ComplaintRaisedMail($complaint));
            }

            return redirect()->route('admin.official-complaints.index')->with('success', 'Official Complaint submitted successfully with ID: ' . $complaint_id);
        }
        catch (\Exception $e) {
            return back()->with('error', 'An error occurred while submitting the official complaint: ' . $e->getMessage());
        }
    }

    public function officialShow($id)
    {
        $complaint = Complaint::with(['user', 'issueType', 'department', 'orders', 'attachments', 'replies.user', 'replies.attachments', 'complaint_user'])
            ->where('type', 'official')
            ->findOrFail($id);

        return view('admin.official_complaints.show', compact('complaint'));
    }
}