<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Morilog\Jalali\Jalalian;

class ContentMetaDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data = parent::toArray($request);

        $data['image_large'] = $this->resource->getImageUrl();

        $data['image_medium'] = $this->resource->getImageUrl('medium');

        $data['image_small'] = $this->resource->getImageUrl('small');

        $data['url'] = $this->resource->getUrl();

        $data['posted_at_jalali'] = Jalalian::fromCarbon(Carbon::parse($data['publish_at']))->format('H:i Y/m/d');

        $data['posted_at_ago'] = $this->translateCarbonDiff(Carbon::parse($data['publish_at']));

//        $data['short_description'] = strip_tags($data['short_description']);

        return $data;
    }

    private function translateCarbonDiff($carbon)
    {
        $get_en = $carbon->diffForHumans();
        $set_fa = str_replace('years', 'سال', $get_en);
        $set_fa = str_replace('months', 'ماه', $set_fa);
        $set_fa = str_replace('weeks', 'هفته', $set_fa);
        $set_fa = str_replace('days', 'روز', $set_fa);
        $set_fa = str_replace('hours', 'ساعت', $set_fa);
        $set_fa = str_replace('minutes', 'دقیقه', $set_fa);
        $set_fa = str_replace('seconds', 'ثانیه', $set_fa);
        $set_fa = str_replace('year', 'سال', $set_fa);
        $set_fa = str_replace('month', 'ماه', $set_fa);
        $set_fa = str_replace('day', 'روز', $set_fa);
        $set_fa = str_replace('week', 'هفته', $set_fa);
        $set_fa = str_replace('hour', 'ساعت', $set_fa);
        $set_fa = str_replace('minute', 'دقیقه', $set_fa);
        $set_fa = str_replace('second', 'ثانیه', $set_fa);
        $set_fa = str_replace('ago', 'پیش', $set_fa);
        $set_fa = str_replace('after', 'بعد', $set_fa);
        $set_fa = str_replace('before', 'قبل', $set_fa);
        $set_fa = str_replace('from now', 'بعد', $set_fa);

        return $set_fa;
    }
}
