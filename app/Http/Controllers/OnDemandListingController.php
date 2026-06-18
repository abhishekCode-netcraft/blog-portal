<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OnDemandListing;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use App\Mail\RequestNotificationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\OnDemandListingLog;


class OnDemandListingController extends Controller
{
    public function __construct()
    {
        $this->middleware('role_or_permission:Product Listing -> Request New Listings (Img)', ['only' => ['index']]);
        $this->middleware('role_or_permission:Product Listing -> Review / Verify Listing (Img)', ['only' => ['verify']]);
    }

    public function index()
    {
        return view('on-demand.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'images.*' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'category' => 'required|in:Create,Update'
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('on-demand', 'public');

                OnDemandListing::create([
                    'requested_by' => auth()->id(),
                    'image' => $path,
                    'category' => $request->category,
                    'status' => 'Requested'
                ]);
            }

            // Create Log Entry
            OnDemandListingLog::create([
                'requested_by' => auth()->id(),
                'count' => count($request->file('images')),
                'category' => $request->category
            ]);

            // Send Email Notification to all Active Users

            $activeUsers = User::where('status', 1)->get();
            // dd($activeUsers);
            $count = count($request->file('images'));
            $data = [
                'subject' => 'New On Demand Images Uploaded',
                'type' => 'On Demand Upload',
                'user_name' => auth()->user()->name,
                'count' => $count,
                'category' => $request->category,
                'details' => "Images uploaded for " . $request->category . " request."
            ];


            foreach ($activeUsers as $user) {
                Mail::to($user->email)->send(new RequestNotificationMail($data));
            }
        }

        session()->flash('success', 'Images uploaded successfully.');
        return redirect()->back();
    }

    public function verify()
    {
        $requestedCreate = OnDemandListing::with('requestedBy')
            ->where('category', 'Create')
            ->where('status', 'Requested')
            ->get();

        $requestedUpdate = OnDemandListing::with('requestedBy')
            ->where('category', 'Update')
            ->where('status', 'Requested')
            ->get();

        $completed = OnDemandListing::with(['requestedBy', 'completedBy'])
            ->where('status', 'Completed')
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('on-demand.verify', compact('requestedCreate', 'requestedUpdate', 'completed'));
    }

    public function complete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:on_demand_listings,id'
        ]);

        OnDemandListing::whereIn('id', $request->ids)->update([
            'status' => 'Completed',
            'completed_by' => auth()->id(),
            'completed_at' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function uncomplete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:on_demand_listings,id'
        ]);

        OnDemandListing::whereIn('id', $request->ids)->update([
            'status' => 'Requested',
            'completed_by' => null,
            'completed_at' => null
        ]);

        return response()->json(['success' => true]);
    }

    public function download($id)
    {
        $listing = OnDemandListing::findOrFail($id);
        $path = storage_path('app/public/' . $listing->image);

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'Image not found.');
        }

        return response()->download($path);
    }

    public function bulkDownload(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:on_demand_listings,id'
        ]);

        $listings = OnDemandListing::whereIn('id', $request->ids)->get();

        if ($listings->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'No images selected.']);
        }

        $zip = new \ZipArchive();
        $zipFileName = 'on-demand-images-' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, \ZipArchive::CREATE) === TRUE) {
            foreach ($listings as $listing) {
                $filePath = storage_path('app/public/' . $listing->image);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, basename($filePath));
                }
            }
            $zip->close();
        }

        return response()->json([
            'success' => true,
            'download_url' => asset('storage/' . $zipFileName)
        ]);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:on_demand_listings,id'
        ]);

        $listings = OnDemandListing::whereIn('id', $request->ids)->get();

        foreach ($listings as $listing) {
            if (Storage::disk('public')->exists($listing->image)) {
                Storage::disk('public')->delete($listing->image);
            }
            $listing->delete();
        }

        return response()->json(['success' => true]);
    }

    public function logs()
    {
        $requestLogs = OnDemandListingLog::with('requestedBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('on-demand.logs', compact('requestLogs'));
    }

    public function bulkTransfer(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:on_demand_listings,id',
            'category' => 'required|in:Create,Update'
        ]);

        OnDemandListing::whereIn('id', $request->ids)->update([
            'category' => $request->category
        ]);

        return response()->json(['success' => true]);
    }
}
