<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ReviewUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = ReviewUser::query();
    
        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
    
        // Search
        if ($request->filled('search')) {
            $search = $request->search;
    
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('google_id', 'like', "%{$search}%")
                  ->orWhere('coupon_code', 'like', "%{$search}%");
            });
        }
    
        // Created Date Range
        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
    
        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
    
        $reviewUsers = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();
    
        return view('review.index', compact('reviewUsers'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'google_id' => 'nullable|string|max:255',
                'full_name' => 'required|string|max:255',
                'email' => ['required', 'email', 'unique:review_users,email',],
                // 'phone' => ['required', 'regex:/^[6-9]\d{9}$/'],
                // 'alternate_phone' => ['nullable', 'regex:/^[6-9]\d{9}$/'],
                'phone' => [ 'required', 'regex:/^[6-9]\d{9}$/'],
                'alternate_phone' => [ 'nullable', 'regex:/^[6-9]\d{9}$/'],
                'coupon_code' => 'nullable|string|max:255',
                'affiliate_partner' => 'required|in:Yes,No',
                'experience_source' => 'required',
                'payout_method' => 'required|in:PhonePe,Google Pay,Paytm,UPI ID',
                'payout_value' => 'required|string|max:255',
                'review_submitted_at' => 'required|date',
                'profile_picture' => 'required',
                'screenshots.*' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:4096',
            ],
            ['email.unique' => "Oops! It looks like you've already claimed a reward.",]
        );
        
        $validator->after(function ($validator) use ($request) {
            if (ReviewUser::where('phone', $request->phone)->orWhere('alternate_phone', $request->phone)->exists()) {
                $validator->errors()->add('phone', "It looks like you've already claimed a reward.");
            }
            if (!empty($request->alternate_phone) && ReviewUser::where('phone', $request->alternate_phone)->orWhere('alternate_phone', $request->alternate_phone)->exists()) {
                $validator->errors()->add('alternate_phone', "It looks like you've already claimed a reward.");
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        /*
         |-------------------------------------------------------------
         | Upload Profile Picture
         |-------------------------------------------------------------
         */
        $profilePicture = null;

        // if ($request->hasFile('profile_picture')) {
        //     $profilePicture = $request
        //         ->file('profile_picture')
        //         ->store('profile-pictures', 'public');
        // }

        /*
         |-------------------------------------------------------------
         | Upload Screenshots
         |-------------------------------------------------------------
         */
        $screenshots = [];

        if ($request->hasFile('screenshots')) {
            foreach ($request->file('screenshots') as $file) {
                $screenshots[] = $file->store('review-screenshots', 'public');
            }
        }

        /*
         |-------------------------------------------------------------
         | Save Data
         |-------------------------------------------------------------
         */
        $reviewUser = ReviewUser::create([
            'google_id'            => $request->google_id,
            'full_name'            => $request->full_name,
            'email'                => $request->email,
            'phone'                => $request->phone,
            'alternate_phone'      => $request->alternate_phone,
            'coupon_code'          => $request->coupon_code,
            'affiliate_partner'    => $request->affiliate_partner,
            'experience_source'    => $request->experience_source,
            'review_submitted_at'  => $request->review_submitted_at,
            'payout_method'        => $request->payout_method,
            'payout_value'         => $request->payout_value,
            'profile_picture'      => $request->profile_picture,
            'screenshot_paths'     => $screenshots,
            'status'               => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Rewards will be granted after 72 hours of successful verification.',
            'data'    => $reviewUser,
        ]);
    }

    public function updateStatus(Request $request, ReviewUser $reviewUser)
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $reviewUser->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully.',
        ]);
    }
    
    public function destroy(ReviewUser $reviewUser)
    {
        try {
    
            // Delete screenshots
            if (!empty($reviewUser->screenshot_paths)) {
    
                foreach ($reviewUser->screenshot_paths as $image) {
    
                    if (Storage::disk('public')->exists($image)) {
                        Storage::disk('public')->delete($image);
                    }
                }
            }
    
            // Delete uploaded profile picture
            if (
                $reviewUser->profile_picture &&
                !filter_var($reviewUser->profile_picture, FILTER_VALIDATE_URL)
            ) {
    
                if (
                    Storage::disk('public')
                        ->exists($reviewUser->profile_picture)
                ) {
    
                    Storage::disk('public')
                        ->delete($reviewUser->profile_picture);
                }
            }
    
            $reviewUser->delete();
    
            return back()->with('success', 'Review deleted successfully');
    
        } catch (\Exception $e) {
    
            return response()->json([
                'success' => false,
                'message' => 'Unable to delete review.',
            ], 500);
        }
    }
}
