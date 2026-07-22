<?php

namespace App\Http\Controllers\Website;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\website\ProfileResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        return view('website.rtl.profile.profile');
    }

    public function init()
    {
        $user = Auth::user();

        return ResponseHelper::basic_response(true, [
            'profile' => ProfileResource::make($user)
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'first_name' => 'required|max:128',
            'last_name' => 'required|max:128',
        ]);

        $user->update($request->only(['email', 'first_name', 'last_name']));

        if ($request->has('birth_day') && $request->get('birth_day') != '') {
            $user->SetData('birth_day', $request->get('birth_day'));
        }

        if ($request->has('address') && $request->get('address') != '') {
            $user->SetData('address', $request->get('address'));
        }

        return ResponseHelper::simple_response(true, 'پروفایل شما با موفقیت ویرایش شد');
    }

    public function following()
    {
        $user = Auth::user();

        return ResponseHelper::basic_response(true, [
            'categories' => $user->categories()->get()->map(function ($item) {
                return [
                    'id' => $item['id'],
                    'fa_name' => $item['fa_name'],
                    'en_name' => $item['en_name'],
                    'url' => route('website.rtl.category',['slug' => $item['slug']])
                ];
            })
        ]);
    }

    public function bookmarks()
    {
        $user = Auth::user();

        return ResponseHelper::basic_response(true, [
            'bookmarks' => $user->bookmarks()->get()->map(function ($item) {
                return $item->getPostTotallyForWebsite(1);
            })
        ]);
    }
}
