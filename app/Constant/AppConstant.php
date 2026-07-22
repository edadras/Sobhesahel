<?php
/**
 * GitHub: RoyalHaze
 * Date: 8/3/24
 * Time: 2:24 PM
 **/

namespace App\Constant;

use App\Filament\Resources\ArchiveResource;
use App\Filament\Resources\GalleryResource;
use App\Filament\Resources\NewsResource;
use App\Filament\Resources\NoteResource;
use App\Filament\Resources\PodcastResource;
use App\Filament\Resources\VideoResource;

class AppConstant
{
    public static function filament_model_map()
    {
        return [
            'news' => NewsResource::class,
            'note' => NoteResource::class,
            'video' => VideoResource::class,
            'podcast' => PodcastResource::class,
            'image' => GalleryResource::class,
            'archive' => ArchiveResource::class,
        ];
    }

    public static function permissions_list()
    {
        return [
            'manage_users' => 'مدیریت کاربران',
            'manage_roles' => 'مدیریت سطح های دسترسی',
            'manage_comments' => 'مدیریت نظرات کاربران',
            'categories' => 'مدیریت دسته بندی ها',
            'new_content' => 'محتوای جدید',
            'edit_content' => 'ویرایش محتوای خود',
            'edit_all_content' => 'ویرایش محتوای سایر کاربران',
            'publish_content' => 'انتشار محتوا بر روی سایت',
            'system_setting' => 'مدیریت تنظیمات سیستم',
            'advertise' => 'مدیریت تبلیغات ها',
            'visits' => 'مشاهده آمار وبسایت',
            'manage_first_page' => 'مدیریت صفحه اصلی',
            'poll' => 'مدیریت نظرسنجی ها',
            'contact' => 'پیام های تماس با ما',
            'customise' => 'شخصی سازی وبسایت',
            'swipe_post_user' => 'ارسال پست با سایر کاربران'
        ];
    }

    public static function website_socials_list()
    {
        return [
            'twitter' => 'x',
            'telegram' => 'تلگرام',
            'youtube' => 'یوتیوب',
            'instagram' => 'اینستاگرام',
            'linkedin' => 'Linkedin'
        ];
    }

    public static function website_meta()
    {
        return [
            'fa_page_title' => [
                'title' => 'تیتر فارسی وبسایت',
                'field' => 'text',
                'direction' => 'rtl'
            ],
            'fa_page_subtitle' => [
                'title' => 'شعار فارسی وبسایت',
                'field' => 'text',
                'direction' => 'rtl'
            ],
            'fa_page_description' => [
                'title' => 'توضیحات فارسی وبسایت',
                'field' => 'textarea',
                'direction' => 'rtl'
            ],
            'en_page_title' => [
                'title' => 'تیتر انگلیسی وبسایت',
                'field' => 'text',
                'direction' => 'ltr'
            ],
            'en_page_subtitle' => [
                'title' => 'شعار انگلیسی وبسایت',
                'field' => 'text',
                'direction' => 'ltr'
            ],
            'en_page_description' => [
                'title' => 'توضیحات انگلیسی وبسایت',
                'field' => 'textarea',
                'direction' => 'ltr'
            ],
        ];
    }

    public static function website_contact()
    {
        return [
            'fa_whatsapp_link' => [
                'title' => 'لینک واتساپ فارسی',
                'field' => 'text',
                'direction' => 'ltr'
            ],
            'fa_address' => [
                'title' => 'آدرس فارسی',
                'field' => 'textarea',
                'direction' => 'rtl'
            ],
            'fa_number' => [
                'title' => 'شماره تلفن فارسی',
                'field' => 'text',
                'direction' => 'ltr'
            ],
            'fa_email' => [
                'title' => 'ایمیل فارسی',
                'field' => 'text',
                'direction' => 'ltr'
            ],
            'en_whatsapp_link' => [
                'title' => 'لینک واتساپ انگلیسی',
                'field' => 'text',
                'direction' => 'ltr'
            ],
            'en_address' => [
                'title' => 'آدرس انگلیسی',
                'field' => 'textarea',
                'direction' => 'ltr'
            ],
            'en_number' => [
                'title' => 'شماره تلفن انگلیسی',
                'field' => 'text',
                'direction' => 'ltr'
            ],
            'en_email' => [
                'title' => 'ایمیل انگلیسی',
                'field' => 'text',
                'direction' => 'ltr'
            ],
        ];
    }

    public static function archives_type()
    {
        return [
            'SobheSahel' => 'روزنامه صبح ساحل',
            'omidsahel' => 'امید ساحل',
            'sobhesahelkids' => 'ضمیمه کودک',
            'SahelBanoo' => 'ماهنامه ساحل بانو',
            'Niazmandiha' => 'نیازمندی ها',
            'Shahrestan' => 'شهرستانها',
            'bastaksobhesahel' => 'ویژه نامه بستک'
        ];
    }

    public static function en_archives_type()
    {
        return [
            'SobheSahel' => 'SobheSahel News Paper',
            'omidsahel' => 'Omid Sahel',
            'sobhesahelkids' => 'SobheSahel Kids',
            'SahelBanoo' => 'Sahel Banoo',
            'Niazmandiha' => 'Niazmandiha',
            'Shahrestan' => 'Shahrestan',
            'bastaksobhesahel' => 'Bastak'
        ];
    }

    public static function post_type_routes($post_type)
    {
        return match ($post_type) {
            'news' => 'news_single',
            'image' => 'gallery_single',
            'video' => 'video_single',
            'podcast' => 'podcast_single',
            'archive' => 'archive.pdf',
            'note' => 'note_single',
            default => '',
        };
    }

    public static function get_single_route_names($post_type)
    {
        return match ($post_type) {
            'news' => 'news',
            'image' => 'photo',
            'video' => 'video',
            'podcast' => 'podcast',
            'archive' => 'archive',
            'note' => 'note',
            default => '',
        };
    }

    public static function email_setting_list()
    {
        return [
            'url' => 'آدرس',
            'port' => '',
            'username' => 'نام کاربری',
            'password' => 'رمز عبور'
        ];
    }

    public static function post_types_title()
    {
        return [
            'news' => 'خبر',
            'image' => 'گالری',
            'video' => 'ویدئو',
            'note' => 'یادداشت',
            'podcast' => 'پادکست',
            'archive' => 'آرشیو'
        ];
    }

    public static function content_status()
    {
        return [
            'published' => [
                'title' => 'منتشر شده',
                'en_title' => 'Published',
                'label' => 'success'
            ],
            'to_publish' => [
                'title' => 'بررسی برای انتشار',
                'en_title' => 'Pending Publication',
                'label' => 'danger'
            ],
            'draft' => [
                'title' => 'پیش نویس',
                'en_title' => 'Draft',
                'label' => 'primary'
            ],
            'to_review' => [
                'title' => 'نیازمند بررسی',
                'en_title' => 'Needs Review',
                'label' => 'warning'
            ],
            'future_publish' => [
                'title' => 'انتشار در آینده',
                'en_title' => 'Scheduled for Future Publication',
                'label' => 'success'
            ],
        ];
    }

    public static function advertise_location()
    {
        return [
            'sidebar_1' => [
                'title' => 'سایدبار بالا'
            ],
            'sidebar_2' => [
                'title' => 'سایدبار پایین'
            ],
            'home_page_1' => [
                'title' => 'تبلیغات عرضی صفحه اصلی'
            ],
            'home_page_2' => [
                'title' => 'تبلیغات کوچک صفحه اصلی'
            ],
            'text' => [
                'title' => 'تبلیغات متنی'
            ]
        ];
    }
}
