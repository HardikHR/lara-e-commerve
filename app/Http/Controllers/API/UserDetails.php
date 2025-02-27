<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\BaseController as ControllersBaseController;
use App\Models\BankDetail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Validator;

class UserDetails extends ControllersBaseController
{   
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'address' => 'required',
            'phone' => 'required|numeric|unique:users',
            'bod' => 'required|date',
            'password' => 'required',
            'c_password' => 'required|same:password',
            
            'bank_details.bank_name' => 'required|string',
            'bank_details.account_number' => 'required|numeric|unique:bank_details',
            'bank_details.ifsc_code' => 'required|string',
        ]);

        if($validator->fails()){
            return response()->json([
                'errors' => $validator->errors()->all()
            ], 422);
        }
        $client = DB::table('oauth_clients')->where('password_client', 1)->first();

        $input = $request->all();
        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);
        $success['token'] = $user->createToken('MyApp')->accessToken;
        $success['name'] = $user->name;
        $success['email'] = $user->email;
        $success['client_id'] = $client->id;
        $success['client_secret'] = $client->secret;

        BankDetail::create([
            'user_id' => $user->id,
            'bank_name' => $request->bank_details['bank_name'],
            'account_number' => $request->bank_details['account_number'],
            'ifsc_code' => $request->bank_details['ifsc_code'],
        ]);
        return response()->json(['data' => $success, 'message' => 'User register successfully.']);
    }

    public function show($id)
    {
        $user = User::with('bankDetails')->find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        return response()->json($user, 200);
    }

    public function update(Request $request, User $user)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'name' => 'required',
            'email' => ['required', Rule::unique('users', 'email')->ignore($user->id)],
            'address' => 'required',
            'phone' => ['required',Rule::unique('users', 'phone')->ignore($user->id)],
            'dob' => 'required|date',
            'password' => 'required',
            'c_password' => 'required|same:password',
        ]);
        if($validator->fails()){
            return response()->json(['message' => $validator->errors()->all()]);
        }
        $user->name = $input['name'];
        $user->email = $input['email'];
        $user->address = $input['address'];
        $user->phone = $input['phone'];
        $user->dob = $input['dob'];
        $user->save();
        return response()->json(['data' => $user, 'message' => 'Profile updated successfully.']);
    }

    public function destroy(User $user)
    {
        $user->deleted = '1';
        $user->save();
        return response()->json(['message' => "$user->name product deleted successfully."]);
    }
    
    public function login(Request $request)
    {           
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){ 
            $user = Auth::user();
            $success['name'] = $user->name;
            $success['message'] = 'User login successfully';
            $success['token'] = $user->createToken('MyApp')->accessToken; 
            return response()->json(['data'=>$success
            ], 200);
        } else { 
            return response()->json(['message' => 'Unauthorised'], 200);
        }
    }
    
    public function logout(Request $request)
    {
        if (Auth::check()) {
            $request->user()->tokens()->delete();
            return response()->json(['message' => 'Successfully logged out'], 200);
        }
        return response()->json(['message' => 'No authenticated user'], 401);
    }
}
