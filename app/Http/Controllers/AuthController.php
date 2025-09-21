<?php

namespace App\Http\Controllers;

use App\Models\AgentWallet;
use App\Models\User;
use App\Services\NetworkLevelService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone_number' => 'nullable|string|max:30',
            'password' => 'required|string|min:6|confirmed',
            'referrer_code' => 'nullable|string|exists:users,agent_code',
        ]);

        // Generate agent code
        $seq = str_pad((string) (User::whereNotNull('agent_code')->count() + 1), 5, '0', STR_PAD_LEFT);
        $agentCode = 'AGT' . $seq;

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone_number' => $data['phone_number'] ?? '0000000000',
                'nric' => '000000000000' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'race' => 'Other',
                'date_of_birth' => '1990-01-01',
                'gender' => 'Male',
                'occupation' => 'Not specified',
                'emergency_contact_name' => 'Emergency Contact',
                'emergency_contact_phone' => '0000000000',
                'emergency_contact_relationship' => 'Family',
                'address' => 'Not specified',
                'city' => 'Not specified',
                'state' => 'Not specified',
                'postal_code' => '00000',
                'password' => Hash::make($data['password']),
                'referrer_code' => $data['referrer_code'] ?? null,
                'agent_code' => $agentCode,
            ]);

        AgentWallet::firstOrCreate(['user_id' => $user->id]);

        // Calculate network levels for the new agent
        try {
            $networkLevelService = new NetworkLevelService();
            $networkLevelService->calculateNetworkLevelsForAgent($user->agent_code);
        } catch (\Exception $e) {
            \Log::error("Failed to calculate network levels for new agent {$user->agent_code}: " . $e->getMessage());
        }

        // Create notifications
        try {
            $notificationService = new NotificationService();
            
            // Welcome notification for new user
            $notificationService->createWelcomeNotification($user->id);
            
            // Notify referrer if exists
            if ($data['referrer_code']) {
                $referrer = User::where('agent_code', $data['referrer_code'])->first();
                if ($referrer) {
                    $notificationService->createNewNetworkMemberNotification($referrer->id, $user->id);
                }
            }
        } catch (\Exception $e) {
            \Log::error("Failed to create notifications for new user {$user->id}: " . $e->getMessage());
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($request->filled('agent_code')) {
            $user = User::where('agent_code', $request->string('agent_code')->upper())->first();
            if (! $user || ! Hash::check($request->string('password'), $user->password)) {
                throw ValidationException::withMessages([
                    'email' => ['Invalid credentials.'],
                ]);
            }
            $token = JWTAuth::fromUser($user);
        } else {
            if (! $token = auth('api')->attempt($credentials)) {
                return response()->json(['status' => 'error', 'message' => 'Invalid credentials'], 401);
            }
        }

        if (! isset($token)) {
            $token = auth('api')->tokenById(auth('api')->user()->id);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => auth('api')->user() ?? $user,
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth('api')->factory()->getTTL() * 60,
            ],
        ]);
    }

    public function me()
    {
        return response()->json(['status' => 'success', 'data' => ['user' => auth('api')->user()]]);
    }

    public function logout()
    {
        auth('api')->logout();
        return response()->json(['status' => 'success', 'message' => 'Logged out']);
    }

    public function refresh()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'authorisation' => [
                    'token' => auth('api')->refresh(),
                    'type' => 'bearer',
                ],
            ],
        ]);
    }
    
    public function updateAgentCode(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'agent_code' => 'required|string|unique:users,agent_code',
        ]);

        $user = User::findOrFail($data['user_id']);
        $user->agent_code = $data['agent_code'];
        // Only set optional columns if they exist in schema
        if (\Schema::hasColumn('users', 'status')) {
            $user->status = 'active';
        }
        if (\Schema::hasColumn('users', 'mlm_activation_date')) {
            $user->mlm_activation_date = now();
        }
        $user->save();

        return response()->json([
            'status' => 'success',
            'data' => [
                'user_id' => $user->id,
                'agent_code' => $user->agent_code,
            ]
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone_number' => 'sometimes|string|max:30',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'address' => 'sometimes|string|max:500',
            'city' => 'sometimes|string|max:100',
            'state' => 'sometimes|string|max:100',
            'postal_code' => 'sometimes|string|max:10',
            'bank_name' => 'sometimes|string|max:100',
            'bank_account_number' => 'sometimes|string|max:50',
            'bank_account_owner' => 'sometimes|string|max:255',
            'occupation' => 'sometimes|string|max:100',
            'height_cm' => 'sometimes|numeric|min:50|max:300',
            'weight_kg' => 'sometimes|numeric|min:20|max:500',
            'emergency_contact_name' => 'sometimes|string|max:255',
            'emergency_contact_phone' => 'sometimes|string|max:30',
            'emergency_contact_relationship' => 'sometimes|string|max:100',
        ]);
        $user->fill($data)->save();
        return response()->json(['status' => 'success', 'data' => ['user' => $user]]);
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);
        $user = auth('api')->user();
        if (! Hash::check($validated['current_password'], $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Current password incorrect'], 422);
        }
        $user->password = Hash::make($validated['new_password']);
        $user->save();
        return response()->json(['status' => 'success']);
    }

    public function sendPhoneVerification(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:30',
        ]);
        
        // In a real application, you would send SMS here
        // For now, we'll just return a success response
        return response()->json([
            'status' => 'success', 
            'message' => 'Verification code sent to ' . $validated['phone_number'],
            'data' => ['verification_code' => '123456'] // Mock code for testing
        ]);
    }

    public function verifyPhoneChange(Request $request)
    {
        $validated = $request->validate([
            'phone_number' => 'required|string|max:30',
            'verification_code' => 'required|string|size:6',
        ]);
        
        // In a real application, you would verify the code here
        // For now, we'll just check if it's our mock code
        if ($validated['verification_code'] !== '123456') {
            return response()->json(['status' => 'error', 'message' => 'Invalid verification code'], 422);
        }
        
        $user = auth('api')->user();
        $user->phone_number = $validated['phone_number'];
        $user->phone_verified_at = now();
        $user->save();
        
        return response()->json(['status' => 'success', 'data' => ['user' => $user]]);
    }
}


