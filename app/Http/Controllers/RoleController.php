<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Initiate the class instance
     *
     * @return void
     */
    function __construct()
    {
        $this->middleware('role_or_permission:Role create', ['only' => ['index', 'show']]);
        $this->middleware('role_or_permission:Role create', ['only' => ['create', 'store']]);
        $this->middleware('role_or_permission:Roles & Permissions -> View All Roles & Permissions', ['only' => ['view']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $roles = Role::latest()->get();

        return view('accounts.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permissions = Permission::get()->toArray();

        foreach ($permissions as $permission) {
            // Check if the user has the current permission
            if (stripos($permission['name'], 'Role') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Role'][] = $permission;
            }

            if (stripos($permission['name'], 'User') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['User'][] = $permission;
            }

            if (stripos($permission['name'], 'Listing') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Listing'][] = $permission;
            }

            if (stripos($permission['name'], 'Settings') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Settings'][] = $permission;
            }

            if (stripos($permission['name'], 'Dashboard') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Dashboard'][] = $permission;
            }

            if (stripos($permission['name'], 'Inventory') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Inventory'][] = $permission;
            }

            if (stripos($permission['name'], 'Image') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Image'][] = $permission;
            }

            if (stripos($permission['name'], 'Post') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Post'][] = $permission;
            }

            if (stripos($permission['name'], 'Job') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Jobs'][] = $permission;
            }

            if (stripos($permission['name'], 'Lead') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Lead/Job Application'][] = $permission;
            }

            if (stripos($permission['name'], 'Marketplace') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Marketplace'][] = $permission;
            }

            if (stripos($permission['name'], 'Marketing') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Marketing'][] = $permission;
            }

            if (stripos($permission['name'], 'QR') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['QR'][] = $permission;
            }

            if (stripos($permission['name'], 'Manager') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Manager'][] = $permission;
            }

            if (stripos($permission['name'], 'Dispute') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Dispute'][] = $permission;
            }
        }

        return view('accounts.roles.create', compact('permissions', 'permissionsInCategory'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate(['name' => 'required']);

        $role = Role::create(['name' => $request->name]);

        $role->syncPermissions($request->permissions);

        session()->flash('success', __("Role created successfully."));

        return redirect()->route('roles.index');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit(Role $role)
    {
        $permissions = Permission::get()->toArray();
        $role->permissions;
        $permissionsInCategory = [];

        foreach ($permissions as $permission) {
            // Check if the user has the current permission
            if (stripos($permission['name'], 'Role') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Role'][] = $permission;
            }

            if (stripos($permission['name'], 'User') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['User'][] = $permission;
            }

            if (stripos($permission['name'], 'Listing') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Listing'][] = $permission;
            }

            if (stripos($permission['name'], 'Settings') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Settings'][] = $permission;
            }

            if (stripos($permission['name'], 'Image') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Image'][] = $permission;
            }

            if (stripos($permission['name'], 'Dashboard') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Dashboard'][] = $permission;
            }

            if (stripos($permission['name'], 'Inventory') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Inventory'][] = $permission;
            }

            if (stripos($permission['name'], 'Post') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Post'][] = $permission;
            }

            if (stripos($permission['name'], 'Job') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Jobs'][] = $permission;
            }

            if (stripos($permission['name'], 'Lead') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Lead/Job Application'][] = $permission;
            }

            if (stripos($permission['name'], 'Marketplace') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Marketplace'][] = $permission;
            }

            if (stripos($permission['name'], 'Marketing') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Marketing'][] = $permission;
            }

            if (stripos($permission['name'], 'QR') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['QR'][] = $permission;
            }

            if (stripos($permission['name'], 'Manager') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Manager'][] = $permission;
            }

            if (stripos($permission['name'], 'Dispute') !== false) {
                // Add the permission to the array for the current category
                $permissionsInCategory['Dispute'][] = $permission;
            }
        }

        return view('accounts.roles.edit', compact('permissions', 'role', 'permissionsInCategory'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Role $role)
    {
        $role->update(['name' => $request->name]);
        $role->syncPermissions($request->permissions);

        session()->flash('success', __("Role updated successfully."));

        return redirect()->route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Role $role)
    {
        $role->delete();

        session()->flash('error', __("Role deleted successfully."));

        return redirect()->route('roles.index');
    }

    /**
     * View Roles and Permissions
     *
     * @return void
     */
    public function view()
    {
        $roles = Role::latest()->get();

        return view('accounts.roles.view', compact('roles'));
    }

    /**
     * Roles Count
     *
     * @return int
     */
    public function rolesCount()
    {
        $roles = Role::latest()->get();

        return $roles->count();
    }
}
