<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Permission;


class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permis = Permission::all();
        return response()->json($permis);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'unique:permissions,name'
            ]
        ]);
        $role = Permission::create(['name' => $request->name]);
        return response()->json(['message' => 'Permission created success','data' => $role]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $permission = Permission::find($id);
        if (is_null($permission)) {
            return response()->json(['message'=> 'Permission not found.']);
        }
        return response()->json(['date' => $permission]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $input = $request->all();
        $validate = Validator::make($input,[
            'name' => [
                'required',
                'string',
                Rule::unique('permissions', 'name')->ignore($request->permission->id)
            ]
        ]);

        if($validate->fails()){
            return response()->json(['message' => $validate->errors()->all()]);
        }

        $permission->name = $input['name'];
        $permission->save();
        return response()->json(['message'=> 'Permission Updated Success.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $permission = Permission::find($id);
        if (!$permission) {
            return response()->json(['message'=> 'Permission not found.']);
        }
        $permission->delete();
        return response()->json(['message' => "$permission->name permission deleted successfully."]);
    }
    
}