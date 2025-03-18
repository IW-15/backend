<?php

namespace App\Http\Middleware;

use App\Helpers\BaseResponse;
use App\Models\User;
use Closure;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class AuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // Periksa apakah token valid, jika tidak maka lempar exception kustom
            $bearer = $request->bearerToken();
            if (!$bearer) {
                throw new AuthenticationException('Authorization required!');
            }

            [$id, $token] = explode('|', $bearer, 2);
            $instance = DB::table('personal_access_tokens')->find($id);
            if (hash('sha256', $token) === $instance->token) {
                if ($user = User::find($instance->tokenable_id)) {
                    if ($user->idUserType == 3) {
                        throw new AuthenticationException("Unauthorized Access");
                    }
                    Auth::login($user);
                    return $next($request);
                }
            }

            throw new AuthenticationException('Unauthorized access.');
        } catch (Exception $e) {
            return BaseResponse::error("Error while authorized token", 401, $e->getMessage());
        }
    }
}
