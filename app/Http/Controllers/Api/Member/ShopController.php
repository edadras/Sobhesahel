<?php

namespace App\Http\Controllers\Api\Member;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\Member\Concerns\MemberApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

/**
 * فروشگاه دیجیتال — بک‌اند محصولات/سفارش‌ها هنوز ساخته نشده است. همه پاسخ‌ها
 * گارد‌شده و خالیِ امن هستند (Schema::hasTable) تا اپ کرش نکند و بعداً پر شوند.
 */
class ShopController extends Controller
{
    use MemberApiResponses;

    /**
     * GET /shop/products?sort= — {data:[], meta}
     */
    public function products(Request $request)
    {
        // جدول products وجود ندارد → لیست خالی امن.
        if (! Schema::hasTable('products')) {
            return $this->emptyPaged();
        }

        return $this->emptyPaged();
    }

    /**
     * GET /shop/products/{slug}
     */
    public function product(Request $request, string $slug)
    {
        return $this->fail('محصول موردنظر یافت نشد.', 404);
    }

    /**
     * POST /shop/orders
     */
    public function createOrder(Request $request)
    {
        return $this->fail('فروشگاه دیجیتال هنوز فعال نشده است.', 422);
    }

    /**
     * GET /shop/orders — {data:[]}
     */
    public function orders(Request $request)
    {
        return $this->data([]);
    }

    /**
     * GET /shop/orders/{id}/download
     */
    public function download(Request $request, int $id)
    {
        return $this->fail('سفارش موردنظر یافت نشد.', 404);
    }
}
