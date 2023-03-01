<?php

namespace App\Http\Controllers\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Cartalyst\Sentinel\Native\Facades\Sentinel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Session;

use Cartalyst\Sentinel\Checkpoints\ThrottlingException;
use Cartalyst\Sentinel\Checkpoints\NotActivatedException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;


class AuthenticationController extends Controller
{

    public function register()
    {
        return view('security.register');
    }

    public function registerUser(Request $request)
    {

        //validate data 
        Sentinel::disableCheckpoints();
        $errorMsgs = [

            'email.required'     =>  'Email id is required',
            'email.email'        =>  'The email must be a valid email',
            'password.required'  =>  'Password is required',
        ];
        $validator = Validator::make($request->all(), [
            'name'        =>  'required',
            'email'       =>  'required|email|unique:users',
            'password'    =>  'required|min:6'
        ], $errorMsgs);

        if ($validator->fails()) {
            $returnData = array(
                'status'     =>  'error',
                'message'    =>  'Please review fields',
                'errors'     =>   $validator->errors()->all()
            ); // validation fail return data in Json format   
            return response()->json($returnData, 500);
        }

        //Remember Check is On 
        if ($request->remember == 'on') {
            try {
                // register use here 
                // $user = User::where('email', '=', $request->email)->first(); 
                $user = Sentinel::registerAndActivate($request->all());
                return back()->with('success', 'You have been registered successfuly');

                dump($user); // if yyou use ddcode will stop after this  
                // after success register dont send to dashbaord 
                // send to login , send use to dashboard after loginn , make sense? 
                return redirect('login');
                //  redirect( url('/dashboard') );
            } catch (ThrottlingException $e) {
                $delay = $e->getDelay();
                $returnData = array(
                    'status'     =>  'error',
                    'message'    =>  'Please review',
                    'errors'     =>   ["you are banned for $delay seconds..."]
                );
                return response()->json($returnData, 500);
            } catch (NotActivatedException $e) {
                $returnData = array(
                    'status'     =>  'error',
                    'message'    =>  'Please review',
                    'errors'     =>   ["Please activate your account."]
                );
                return response()->json($returnData, 500);
            }
        } else {
            try {
                $user = Sentinel::registerAndActivate($request->all());
                return back()->with('success', 'You have been registered successfuly');

                dd($user);

                //   redirect( url('/dashboard') );
                return redirect('login');
            } catch (ThrottlingException $e) {
                $delay = $e->getDelay();
                $returnData = array(
                    'status'     =>  'error',
                    'message'    =>  'Please review',
                    'errors'     =>   ["you are banned for $delay seconds..."]
                );
                return response()->json($returnData, 500);
            } catch (NotActivatedException $e) {
                $returnData = array(
                    'status'     =>  'error',
                    'message'    =>  'Please review',
                    'errors'     =>   ["Please activate your account."]
                );
                return response()->json($returnData, 500);
            }
        }
    }

    //Login Logic
    public function login()
    {
        return view('security.login');
    }

    public function loginUser(Request $request)
    {

        Sentinel::disableCheckpoints();
        // dd($request);
        $errorMsgs = [
            'email.required'     =>  'Email id is required',
            'email.email'        =>  'The email must be a valid email',
            'password.required'  =>  'Password is required',
        ];

        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|min:6'
        ], $errorMsgs);

        if ($validator->fails()) {
            $returnData = array(
                'status'     =>  'error',
                'message'    =>  'Please review fields',
                'errors'     =>   $validator->errors()->all()
            ); // validation fail return data in Json format   
            return response()->json($returnData, 500);
        
        }
        
        //Remember Check is On 
        if ($request->remember == 'on') {
            dd('im inside if '); 
            try {
                $user = Sentinel::authenticateAndRemember($request->all());
                if (!$user)
                {
                    return back()->with('fail', 'Credentials are wrong.');
                }
                // dd($user);
            } catch (ThrottlingException $e) {

                return back()->with('fail', 'you are banned for $delay seconds');
            } catch (NotActivatedException $e) {
                return back()->with('fail', 'Please activate your account.');
            }
        } else {
           
            try {
                $user = Sentinel::authenticate($request->all());
                if (!$user)
                {
                    return back()->with('fail', 'Credentials are wrong.');
                }
            } catch (ThrottlingException $e) {

                return back()->with('fail', 'you are banned for some seconds...');
            } catch (NotActivatedException $e) {
                return back()->with('fail', 'Please activate your account.');
            }
        }

       

        //User has logedIn
        if (Sentinel::check()) {
          
            return redirect('dashboard');
            // echo'Test';
        } else {

            return back()->with('fail', 'Credentials mismatched.');
        }
    }

    //Dashboard
    public function dashboard()
    {
        dump('i am in dashboard'); 
        dd(Sentinel::check());

        // if(Sentinel::check()){
        return view('products\dashboard');
    }

    //Logout method
    public function logout()
    {
        Sentinel::logout();
        return redirect(url('/login'));
    }
}
