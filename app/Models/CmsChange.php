<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Sushi\Sushi;

class CmsChange extends Model
{
    use Sushi;

    public function getRows(): array
    {
        return [
            ['id' => 2 ,'title' => 'حل مشکل پست های دارای اسلش در عنوان','date' => '1404/01/05'],
            ['id' => 1 ,'title' => 'تغییر کپچا به reCaptcha','date' => '1404/01/03'],
            [
                'id' => 3,
                'title' => 'فیکس شدن باگ آپلود آرشیو',
                'date' => '1404/01/18'
            ],
            [
                'id' => 4,
                'title' => 'اضافه شدن کلید به آدرس لاگین',
                'date' => '1404/01/18'
            ],


        ];
    }
}
