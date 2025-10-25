<?php namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Traits\HttpResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class UserController extends Controller
{
    use HttpResponses;
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $users = UserResource::collection(User::all()->sortByDesc('created_at'));

        if(!empty($users))
            return $this->success($users, 'Users fetched successfully.');
        else
            return $this->error([], 'No users found.', 404);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Delete the user's account and all associated data.
     */
    public function deleteAccount(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            Log::info('Starting account deletion for user: ' . $user->id);
            Log::info('User data:', ['user' => $user->toArray()]);
            
            // Start transaction
            DB::beginTransaction();
            
            try {
                // Delete points if exists
                if ($user->points) {
                    Log::info('Deleting points for user: ' . $user->id);
                    Log::info('Points data:', ['points' => $user->points->toArray()]);
                    $user->points->delete();
                    Log::info('Points deleted successfully');
                } else {
                    Log::info('No points found for user: ' . $user->id);
                }

                // Delete profile if exists
                if ($user->profile) {
                    Log::info('Deleting profile for user: ' . $user->id);
                    Log::info('Profile data:', ['profile' => $user->profile->toArray()]);
                    $user->profile->delete();
                    Log::info('Profile deleted successfully');
                } else {
                    Log::info('No profile found for user: ' . $user->id);
                }

                // Delete cars if exists
                if ($user->cars()->exists()) {
                    Log::info('Deleting cars for user: ' . $user->id);
                    $cars = $user->cars()->get();
                    Log::info('Cars data:', ['cars' => $cars->toArray()]);
                    $user->cars()->delete();
                    Log::info('Cars deleted successfully');
                } else {
                    Log::info('No cars found for user: ' . $user->id);
                }

                // Delete tokens
                if ($user->tokens()->exists()) {
                    Log::info('Deleting tokens for user: ' . $user->id);
                    $tokens = $user->tokens()->get();
                    Log::info('Tokens data:', ['tokens' => $tokens->toArray()]);
                    $user->tokens()->delete();
                    Log::info('Tokens deleted successfully');
                } else {
                    Log::info('No tokens found for user: ' . $user->id);
                }

                // Finally delete the user
                Log::info('Deleting user: ' . $user->id);
                $user->delete();
                Log::info('User deleted successfully');
                
                DB::commit();
                Log::info('Account deletion completed successfully');
                
                return $this->success([], 'Account deleted successfully');
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Error during deletion: ' . $e->getMessage());
                Log::error('Stack trace: ' . $e->getTraceAsString());
                Log::error('Error occurred in file: ' . $e->getFile() . ' on line: ' . $e->getLine());
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Account deletion failed: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . ' Line: ' . $e->getLine());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return $this->error(
                [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ],
                'Failed to delete account',
                500
            );
        }
    }
}
