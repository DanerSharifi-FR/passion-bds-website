<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\LoginCodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CodeAuthController extends Controller
{
    /**
     * @throws ValidationException
     */
    public function requestCode(Request $request, LoginCodeService $service): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'max:255'],
        ]);

        $service->requestCode(
            email: $request->string('email')->toString(),
            ip: $request->ip(),
            userAgent: $request->userAgent(),
        );

        return response()->json(['ok' => true], 200);
    }

    /**
     * @throws ValidationException
     */
    public function verifyCode(Request $request, LoginCodeService $service): JsonResponse
    {
        $request->validate([
            'email' => ['required', 'string', 'max:255'],
            'code'  => ['required', 'string', 'max:10'],
        ]);

        $user = $service->verifyCode(
            email: $request->string('email')->toString(),
            code: $request->string('code')->toString(),
            ip: $request->ip(),
        );

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return response()->json([
            'ok' => true,
            'user' => [
                'id' => $user->id,
                'email' => $user->university_email,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect(route('home'));
    }
}
