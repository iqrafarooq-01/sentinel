<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Support\Facades\Log;
class AuthCheck 
{
    /**
     * Handle an incoming request. you are using sentinal right? they why are still checking with Login id 
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sentinel check True
        if (Sentinel::guest()) {
            // dd((Sentinel::check()));
            /// this code block will run if sentinel check is false 
            // one more debugin trick 
            Log::info('auth check false '); 
            
           
            return redirect('login')->with('fail', 'You have to login first');
        }
        return $next($request);
    }
}
