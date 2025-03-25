<?php

namespace App\Http\Controllers\admin;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Permission;

use Hash;
// use App\Models\Role;

use DB;

class AdminController extends Controller
{
    //
    public function dashboard(){
        return view('admin.dashboard');
    }
    
    public function general_dashboard(){
        return view('admin.general.index');
    }


}
