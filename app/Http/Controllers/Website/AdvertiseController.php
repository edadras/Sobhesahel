<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Advertise;
use Illuminate\Http\Request;

class AdvertiseController extends Controller
{
    public function index($id)
    {
        $advertise = Advertise::findOrFail($id);

        $advertise->increment('click');

        return redirect($advertise['url']);
    }
}
