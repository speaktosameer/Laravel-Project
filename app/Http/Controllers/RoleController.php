<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use DB;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
         $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index','store']]);
         $this->middleware('permission:role-create', ['only' => ['create','store']]);
         $this->middleware('permission:role-edit', ['only' => ['edit','update']]);
         $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $roles = Role::orderBy('id','DESC')->paginate(5);
        return view('super-admin/roles.index', [
            'theme' => 'light',
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => 'side-menu',
            'roles'=>$roles,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/roles').'" class="breadcrumb--active">Roles</a>'
            
        ]);

    }
    
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $permission = Permission::get();

        return view('super-admin/roles.create', [
            'theme' => 'light',
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => 'side-menu',
            'permission'=>$permission,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/roles/').'" class="breadcrumb">Roles</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/roles/create').'" class="breadcrumb--active">Add Roles</a>'
            
        ]);
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|unique:roles,name',
            'permission' => 'required',
        ]);
        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($request->input('permission'));
        return back()->with('success',__('message_alerts.record_updated'));
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $role = Role::find($id);
        $rolePermissions = Permission::join("role_has_permissions","role_has_permissions.permission_id","=","permissions.id")
            ->where("role_has_permissions.role_id",$id)
            ->get();
        return view('super-admin/roles.show',compact('role','rolePermissions'));
    }
    
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($layout = 'side-menu', $theme = 'light', $id)
    {
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id",$id)
            ->pluck('role_has_permissions.permission_id','role_has_permissions.permission_id')
            ->all();
            return view('super-admin/roles.edit', [
            'theme' => $theme,
            'page_name' => 'index',
            'side_menu' => array(),
            'layout' => $layout,
            'role'=>$role,
            'permission'=>$permission,
            'rolePermissions'=>$rolePermissions,
            'breadcrumb'=>'<a href="'.url('/').'" class="breadcrumb">Dashboard</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="'.url('/sub-admin/side-menu/light').'" class="breadcrumb">Sub Admin</a><i data-feather="chevron-right" class="breadcrumb__icon"></i><a href="javascript:;" class="breadcrumb--active">Edit Roles</a>'
        ]);
    }
    
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'name' => 'required',
            'permission' => 'required',
        ]);
        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();
        $role->syncPermissions($request->input('permission'));
        return back()->with('success',__('message_alerts.record_updated'));
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::table("roles")->where('id',$id)->delete();
        return back()->with('success',__('message_alerts.role_deleted_success'));
    }
}
