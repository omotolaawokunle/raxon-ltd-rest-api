<?php

namespace App\Http\Controllers;

use Auth;
use App\User;
use Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $status = 401;
        $response = ['error' => 'Unauthorized'];

        if (Auth::attempt($request->only(['phone', 'password']))) {
            $status = 200;
            $response = [
                'user' => Auth::user(),
                'message' => 'A verification code has been sent to your phone. Please check and verify.',
                'token' => Auth::user()->createToken('raxon')->accessToken,
            ];
        }

        return response()->json($response, $status);
    }

    public function register(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|max:50',
                'business_name' => 'required|max:50',
                'address' => 'required',
                'city' => 'required',
                'state' => 'required',
                'service' => 'required',
                'phone' => 'required|string|unique:users',
                'password' => 'required|string|min:6',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }

        $data = $request->only(['name', 'business_name', 'address', 'city', 'state', 'service', 'phone', 'password']);
        $data['password'] = bcrypt($data['password']);

        $response = (new \therealsmat\Ebulksms\EbulkSMS())->composeMessage($message)
            ->addRecipients($data['phone'])
            ->send();
        if($response['status'] == "MISSING_RECIPIENT" || $response['status'] == "INVALID_RECIPIENT" {
            return response()->json(["message" => "Invalid Phone Number"], 401);
        }

        $user = User::create($data);
        $code = random_int(1000, 9999);
        $message = 'Hi! Your Raxon verification code is ' . $code . '.';
        
        $user->forceFill([
            'verification_code' => $code,
        ])->save();

        return $this->login($request);
    }

    public function search(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'search' => 'required|string',
                'location' => 'string',
                'service' => 'string'
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $search = $request->search;
        if ($request->location) {
            $search = $search . ', ' . $request->location;
        }
        if ($request->service) {
            $search = $search . ', ' . $request->service;
        }
        $searchResults = (new \Spatie\Searchable\Search())
            ->registerModel(User::class, ['business_name', 'service', 'address', 'city', 'state'])
            ->search($search);

        return response()->json(['results' => $searchResults], 200);
    }

    public function verifyPhoneNumber(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'code' => 'required',
            ]
        );
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 401);
        }
        $user = Auth::user();
        if (!$user->hasVerifiedPhone()) {
            if ($request->code == $user->verification_code) {
                $user->markPhoneAsVerified();
                return response()->json(['message' => 'Phone verified successfully', 'user' => $user], 200);
            } else {
                return response()->json(['message' => 'Invalid Code!'], 401);
            }
        } else {
            return response()->json(['message' => 'User has already been verified!'], 401);
        }
    }

    public function artisanProfile()
    {
        return response()->json(['user' => Auth::user()], 200);
    }
}
