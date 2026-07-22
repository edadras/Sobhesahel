<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Advertise extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public static function getImageAdvertise($title,$lang_id = 1)
    {
        $data = setting($title);

        if ($data == null || count($data) == 0){
            return null;
        }


            $ad_object = self::getRandomElement($data);

            $ad = self::findOrFail($ad_object);

            $ad->url = route('advertise_click',['id' => $ad->id]);

//            $ad->increment('view');

            return $ad;

    }

    public static function getTextAdvertise($lang_id = 1)
    {
        $data = setting('text_advertise');

        if ($data == null || count($data) == 0){
            return null;
        }

        $advertises = self::whereIn('id',$data)->get();

        return $advertises;
    }

    private static function getRandomElement(array $arr)
    {
        if (count($arr) === 0) {
            return null;
        }

        return $arr[array_rand($arr)];
    }

    public function delete_advertise()
    {
        try {
            $this->remove_advertise_from_locations();

            $this->delete();
            return true;
        }catch (\Exception $e){
            return false;
        }
    }

    public function remove_advertise_from_locations()
    {
        $setting = AppSetting::getAdvertiseSetting($this->lang_id);

        foreach ($setting as $banner_key => $item){
            foreach ($item as $key => $ad_object){
                if ($ad_object['id'] == $this->id){
                    unset($setting[$banner_key][$key]);
                }
            }
        }

        AppSetting::update_or_insert('advertise_' . $this->lang_id,null,$setting);

        Cache::forget('app_setting_advertise_1');
        Cache::forget('app_setting_advertise_2');
    }

    public function handle_click($id)
    {

    }

    public function handle_advertise_cronjob()
    {
        if ($this->max_view != 0 && $this->max_view <= $this->view){
            $this->update(['is_active' => false]);
        }

        if ($this->max_click != 0 && $this->max_click <= $this->click){
            $this->update(['is_active' => false]);
        }
    }
}

