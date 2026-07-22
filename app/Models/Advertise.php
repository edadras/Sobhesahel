<?php

namespace App\Models;

use App\Support\AdvertisePositionConfig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Outerweb\Settings\Models\Setting;

class Advertise extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        static::deleted(function (Advertise $advertise) {
            $advertise->removeFromAllPositions();
        });
    }

    public static function getImageAdvertise($title,$lang_id = 1)
    {
        $data = setting($title);

        if ($data == null || count($data) == 0){
            return null;
        }

        $ads = self::whereIn('id',$data)
            ->where('is_active',true)
            ->whereNotNull('image')
            ->get();

        if ($ads->isEmpty()){
            return null;
        }

        $ad = $ads->random();

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

        $advertises = self::whereIn('id',$data)->where('is_active',true)->get();

        return $advertises;
    }

    /**
     * Setting keys of the positions this advertise is currently assigned to.
     */
    public static function getPositionsForAd($adId): array
    {
        if ($adId == null){
            return [];
        }

        $positions = [];

        foreach (AdvertisePositionConfig::allPositionKeys() as $key){
            $ids = array_map('intval',(array) (setting($key) ?? []));

            if (in_array((int) $adId,$ids,true)){
                $positions[] = $key;
            }
        }

        return $positions;
    }

    /**
     * Assign this advertise to the given position keys (and remove it
     * from every other position of the same type).
     */
    public function syncPositions(?array $positions): void
    {
        $positions = $positions ?? [];

        $available = $this->image != null
            ? array_keys(AdvertisePositionConfig::IMAGE_POSITIONS)
            : array_keys(AdvertisePositionConfig::TEXT_POSITIONS);

        foreach ($available as $key){
            $ids = array_map('intval',(array) (setting($key) ?? []));
            $ids = array_values(array_diff($ids,[(int) $this->id]));

            if (in_array($key,$positions,true)){
                $ids[] = (int) $this->id;
            }

            Setting::set($key,array_values(array_unique($ids)));
        }
    }

    /**
     * Remove this advertise from every position it is assigned to.
     */
    public function removeFromAllPositions(): void
    {
        foreach (AdvertisePositionConfig::allPositionKeys() as $key){
            $ids = array_map('intval',(array) (setting($key) ?? []));
            $filtered = array_values(array_diff($ids,[(int) $this->id]));

            if (count($filtered) !== count($ids)){
                Setting::set($key,$filtered);
            }
        }
    }

    public function delete_advertise()
    {
        try {
            // Positions are cleaned up by the "deleted" model event.
            $this->delete();
            return true;
        }catch (\Exception $e){
            return false;
        }
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

