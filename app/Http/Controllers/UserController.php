<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserRequest;
use App\Mail\UserMail;
use App\Models\User;
use App\Models\UserSession;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Models\UserListingCount;
use App\Mail\RegisterOtpMail;
use App\Mail\WelcomeMail;
use App\Mail\StatusNotificationMail;
use App\Models\WeightVSCourier;
use Illuminate\Http\Request;
use App\Models\CreatePage;
class UserController extends Controller
{

    public function __invoke($file)
    {
        dd('ad');
        abort_if(auth()->guest(), Response::HTTP_FORBIDDEN);
    }
    /**
     * Initiate the class instance
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware('role_or_permission:User Details (Main Menu)|User create|User Details -> All Users List -> Edit|User delete', ['only' => ['index', 'show']]);
        $this->middleware('role_or_permission:User New create', ['only' => ['create', 'store']]);
        $this->middleware('role_or_permission:User Details -> All Users List -> Edit', ['only' => ['edit', 'update']]);
        $this->middleware('role_or_permission:User delete', ['only' => ['destroy']]);
        $this->middleware('role_or_permission:Inventory -> Counts Report', ['only' => ['userCounts']]);
        $this->middleware('role_or_permission:Allot User Roles', ['only' => ['verified']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Developer');
        })
            ->when(request()->filled('status'), function ($query) {
                $query->where('status', request('status'));
            })
            ->withSum(['counts as total_created' => function ($query) {
                $query->where('status', 'Created');
            }], 'create_count')
            ->withSum(['counts as total_updated' => function ($query) {
                $query->where('status', 'Edited');
            }], 'create_count')
            ->orderBy('id', 'asc')
            ->paginate(request()->get('users', 10));


        $allUser = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Developer');
        })->count();

        $active = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Developer');
        })
            ->where('status', 1)->count();

        $inactive = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Developer');
        })
            ->where('status', 0)->count();

        $suspended = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Developer');
        })
            ->where('status', 2)->count();

        $blocked = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Developer');
        })
            ->where('status', 3)->count();

        $withoutRoles = User::whereDoesntHave('roles', function ($query) {
            $query->where('name', 'Developer');
        })
            ->whereDoesntHave('roles')
            ->count();

        return view('accounts.users.index', compact('users', 'allUser', 'active', 'inactive', 'suspended', 'blocked', 'withoutRoles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::get();

        return view('accounts.users.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        User::create($request->validated())?->syncRoles(request()->input('roles'));

        Mail::to($request->email)->send(new UserMail($request->all()));

        session()->flash('success', __('User created successfully.'));

        return redirect()->route('users.index');
    }

    /**
     * Generate Random Digits
     *
     * @return int
     */
    public function generateOTP($n)
    {
        $generator = "1357902468";
        $result = "";

        for ($i = 1; $i <= $n; $i++) {
            $result .= substr($generator, rand() % strlen($generator), 1);
        }

        return $result;
    }

    public function registerOTP(Request $request)
    {
        $otp = $this->generateOTP(6);

        session()->flash('otp', $otp);

        // Collect all form data
        $userData = $request->only([
            'name',
            'email',
            'mobile',
            'account_type',
            'aadhaar_no',
            'father_name',
            'mother_name',
            'state',
            'pincode',
            'full_address'
        ]);

        Mail::to('abhishek86478@gmail.com')->send(new RegisterOtpMail($otp, $userData));

        session()->flash('success', __('OTP Send to Admin'));

        return view('auth.enter-otp', ['register' => true]);
    }

    public function register()
    {
        if (session('otp') == request()->otp) {
            User::create(request()->all())?->syncRoles(request()->input('roles'));

            session()->flash('success', __('You have Registered Successfully...'));

            return redirect()->route('login');
        } else {
            session()->flash('success', __('Invalid OTP'));

            return view('auth.enter-otp', ['register' => true]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): \Illuminate\View\View
    {
        $user = User::find($id);

        $roles = Role::get();

        return view('accounts.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(string $id)
    {
        $user = User::find($id);

        $user->update(request()->all());

        if (request()->input('roles')) {
            $user->syncRoles(request()->input('roles'));
        }

        session()->flash('success', __('User updated successfully.'));

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::find($id);

        $user?->delete();

        session()->flash('success', __('User delete successfully.'));

        return redirect()->route('users.index');
    }

    /**
     * Get verified Users
     *
     * @return void
     */
    public function verified()
    {
        $users = User::orderBy('id', 'asc')
            ->paginate(request()->users);

        return view('accounts.users.approved', compact('users'));
    }

    /**
     * Update the Password
     *
     * @return void
     */
    public function updatePassword()
    {
        try {
            if (!request()->current) {
                return view('accounts.users.change-password');
            }

            $this->validate(request(), [
                'current' => 'required',
                'new'     => 'required',
            ]);

            $hashedPassword = Hash::make(request()->new);

            $user = Auth::user();

            // Check if the entered password matches the hashed password
            if (Hash::check(request()->current, $user->password)) {
                $user->update([
                    'password' => $hashedPassword,
                    'plain_password' => request()->new
                ]);

                session()->flash('success', __('Password updated successfully'));

                return redirect()->route('dashboard');
            }
            session()->flash('success', __('Please check your current password'));

            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('success', __('An error occurred on the server. Please try again later'));

            return redirect()->back();
        }
    }

    /**
     * Edit Status
     *
     * @return void
     */
    public function editStatus($id)
    {
        $roles = Role::get();

        $user = User::find($id);

        return view('accounts.users.update', compact('roles', 'user'));
    }

    /**
     * Update Status
     *
     * @return void
     */
    public function updateStatus($id)
    {
        $user = User::find($id);

        $status = $user->status;

        $user->update(request()->all());

        $user->syncRoles(request()->input('roles'));

        if (!$status) {
            Mail::to($user->email)->send(new WelcomeMail($user));

            Mail::to('abhishek86478@gmail.com')->send(new WelcomeMail($user));
        }

        if ($user->status == 2) {
            $subject = "Urgent: Your Account Has Been Suspended Due to Unusual Activities";
            $body = "We regret to inform you that your account has been Suspended due to unusual activities detected on our platform. This decision has been made to ensure the safety and integrity of our users and services.";
            Mail::to($user->email)->send(new StatusNotificationMail('SUSPEND', $subject, $body));
            Mail::to('abhishek86478@gmail.com')->send(new StatusNotificationMail('SUSPEND', $subject, $body));
        } else if ($user->status == 3) {
            $subject = "Important Notice: Your Account Has Been Permanently Blocked";
            $body = "We regret to inform you that your account has been permanently blocked due to unusual activities detected on our platform. This decision has been made to ensure the safety and integrity of our users and services. ";
            Mail::to($user->email)->send(new StatusNotificationMail('BLOCKED', $subject, $body));
            Mail::to('abhishek86478@gmail.com')->send(new StatusNotificationMail('BLOCKED', $subject, $body));
        }

        session()->flash('success', __('User updated successfully.'));

        return redirect()->route('verified.users');
    }

    /**
     * Set Session
     *
     * @return void
     */
    public function setSessionId()
    {
        $sessionId = UserSession::where('user_id', auth()->user()->id)
            ->orderBy('id', 'DESC')
            ->first();

        $currentDateTime = Carbon::now();
        $sessionExpireDateTime = $currentDateTime->addMinutes(env('SESSION_LIFETIME'));

        $sessionId->update([
            'expire_at' => $sessionExpireDateTime
        ]);

        session()->put('session_id', $sessionId->session_id);

        return true;
    }

    /**
     * Sessions Delete
     */
    public function deleteSessionId($id)
    {
        UserSession::where('session_id', $id)->delete();

        if (request()->ajax()) {
            return response()->json([
                'status' => true
            ]);
        }

        session()->flash('success', 'Session Deleted Succesfully');

        return redirect()->back();
    }

    public function userCounts()
    {
        // Initialize query for 'Created' status and group by user
        // $countCreated = UserListingCount::with('user')
        //     ->selectRaw('user_id, sum(create_count) as total_created, sum(approved_count) as total_approved, sum(reject_count) as total_rejected, sum(delete_count) as total_deleted')
        //     ->where('status', 'Created')
        //     ->groupBy('user_id');

        // if (request()->has('start_date') && request()->has('end_date')) {
        //     $startDate = Carbon::parse(request()->start_date);
        //     $endDate = Carbon::parse(request()->end_date);

        //     $countCreated->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
        // }

        // if (request()->user_id) {
        //     $countCreated->where('user_id', request()->user_id);
        // }

        // if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Super Management')) {
        //     $countCreated->where('user_id', auth()->user()->id);
        // }

        // $countCreated = $countCreated->get();

        // // Initialize query for 'Edited' status and group by user
        // $countEdited = UserListingCount::with('user')
        //     ->selectRaw('user_id, sum(create_count) as total_created, sum(approved_count) as total_approved, sum(reject_count) as total_rejected, sum(delete_count) as total_deleted')
        //     ->where('status', 'Edited')
        //     ->groupBy('user_id');

        // if (request()->has('start_date') && request()->has('end_date')) {
        //     $startDate = Carbon::parse(request()->start_date);
        //     $endDate = Carbon::parse(request()->end_date);

        //     $countEdited->whereBetween('created_at', [$startDate->startOfDay(), $endDate->endOfDay()]);
        // }

        // if (request()->user_id) {
        //     $countEdited->where('user_id', request()->user_id);
        // }

        // if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Super Management')) {
        //     $countEdited->where('user_id', auth()->user()->id);
        // }

        // $countEdited = $countEdited->get();

        $countCreated = $this->getListingCountByStatus('Created');
        $countEdited  = $this->getListingCountByStatus('Edited');
        $countPriceIssue  = $this->getListingCountByStatus('Price Issue');


        // Get all users
        $users = User::all();

        // Create Page Report
        $createPageReport = CreatePage::with('user')
            ->selectRaw("
                user_id,
                COUNT(*) as total_created,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS total_approved,
                SUM(CASE WHEN status = 'denied' THEN 1 ELSE 0 END) AS total_rejected,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS total_pending
            ")
            ->groupBy('user_id');


        if (request()->has('start_date') && request()->has('end_date')) {
            $start = Carbon::parse(request()->start_date)->startOfDay();
            $end = Carbon::parse(request()->end_date)->endOfDay();

            $createPageReport->whereBetween('created_at', [$start, $end]);
        }


        if (request()->user_id) {
            $createPageReport->where('user_id', request()->user_id);
        }

        if (!auth()->user()->hasRole('Super Admin') && !auth()->user()->hasRole('Super Management')) {
            $createPageReport->where('user_id', auth()->user()->id);
        }

        $createPageReport = $createPageReport->get();
        // End Create Page Report

        // Pass data to the view
        return view('counts', compact('countCreated', 'countEdited', 'createPageReport', 'users', 'countPriceIssue'));
    }

    private function getListingCountByStatus(string $status)
    {
        $query = UserListingCount::with('user')
            ->selectRaw('
            user_id,
            SUM(create_count)   as total_created,
            SUM(approved_count) as total_approved,
            SUM(reject_count)   as total_rejected,
            SUM(delete_count)   as total_deleted
        ')
            ->where('status', $status)
            ->groupBy('user_id');

        // Date filter
        if (request()->filled(['start_date', 'end_date'])) {
            $query->whereBetween('created_at', [
                Carbon::parse(request()->start_date)->startOfDay(),
                Carbon::parse(request()->end_date)->endOfDay(),
            ]);
        }

        // User filter
        if (request()->user_id) {
            $query->where('user_id', request()->user_id);
        }

        // Role-based restriction
        if (!auth()->user()->hasAnyRole(['Super Admin', 'Super Management'])) {
            $query->where('user_id', auth()->id());
        }

        return $query->get();
    }


    public function priceCalculation()
    {
        $publications  = WeightVSCourier::all();

        $password = env('PUBLIC_CALCULATOR_PASSWORD'); // set your password

        // If already unlocked in session
        if (request()->session()->get('page_unlocked', false)) {
            return view('price-calculator', compact('publications'));
        }

        // Check password submission
        if (request()->isMethod('post') && request()->input('page_password') === $password) {
            request()->session()->put('page_unlocked', true);
            return view('price-calculator', compact('publications'));
        }

        // Otherwise show password form
        return view('password-protect');
    }
}
