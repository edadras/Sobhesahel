<?php

namespace App\Http\Controllers\Website;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        if (Auth::check()){
            return redirect()->route('profile');
        }

        return view('website.rtl.login');
    }

    public function otp(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|numeric|digits:11'
        ]);

        $mobile = $request->get('mobile');

        $user = User::where('mobile', $mobile)->first();

        if ($user == null) {
            $user = User::create([
                'mobile' => $mobile
            ]);
        }

        $res = $user->sendOtp();

        if ($res['status']) {
            $message = ($res['is_send']) ? 'کد تایید برای شما ارسال شد' : 'کد تایید به تازگی برای شما ارسال شده ، لطفا منتظر بمانید';
        } else {
            $message = 'خطایی در ارسال کد تایید رخ داده';
        }

        return ResponseHelper::basic_response($res['status'], array_merge($res, [
            'message' => $message
        ]));
    }

    public function verify(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|numeric|digits:11',
            'code' => 'required|numeric|digits:6'
        ]);

        try {
            $user = User::where('mobile',$request->get('mobile'))->firstOrFail();
        }catch (\Exception $e){
            return ResponseHelper::basic_response(false,[
               'reset_form' => true
            ]);
        }

        try {
            $otp = $user->Otp()->where('code', $request->get('code'))->firstOrFail();
        }catch (\Exception $e){
            return ResponseHelper::basic_response(false,[
                'reset_form' => false,
                'message' => 'کد تایید وارد شده صحیح نمیباشد'
            ]);
        }

        $res = $otp->isValid();

        if ($res){
            $otp->update([
               'expire_time' => Carbon::parse($otp->expire_time)->addSeconds(20)
            ]);

            return ResponseHelper::basic_response(true,[
                'reset_form' => false,
                'redirect' => route('otp_direct_login')
            ]);
        }else{
            return ResponseHelper::basic_response(false,[
                'reset_form' => true,
                'message' => 'کد تایید وارد شده منقضی شده'
            ]);
        }
    }

    public function create_cookie(Request $request)
    {
        $this->validate($request, [
            'mobile' => 'required|numeric|digits:11',
            'code' => 'required|numeric|digits:6'
        ]);

        try {
            $user = User::where('mobile',$request->get('mobile'))->firstOrFail();
        }catch (\Exception $e){
            return redirect()->route('profile');
        }

        try {
            $otp = $user->Otp()->where('code', $request->get('code'))->firstOrFail();
        }catch (\Exception $e){
            return redirect()->route('profile');
        }

        $res = $otp->isValid();

        if ($res){
            $otp->OtpUsed();

            Auth::login($user);

            return redirect()->route('profile');
        }else{
            return redirect()->route('profile');
        }
    }
}
