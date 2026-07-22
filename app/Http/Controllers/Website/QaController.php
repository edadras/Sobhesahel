<?php

namespace App\Http\Controllers\Website;

use App\Http\Controllers\Controller;
use App\Models\Question;
use App\Models\QuestionCategory;
use Illuminate\Http\Request;

class QaController extends Controller
{
    public function index(Request $request)
    {
        $categories = QuestionCategory::active()
            ->orderBy('sort')
            ->orderBy('title')
            ->get();

        $search = trim((string) $request->get('q', ''));
        $category_slug = trim((string) $request->get('category', ''));

        $query = Question::published()
            ->with('category')
            ->withCount('published_answers');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('body', 'LIKE', "%{$search}%");
            });
        }

        $current_category = null;

        if ($category_slug !== '') {
            $current_category = $categories->firstWhere('slug', $category_slug);

            if ($current_category) {
                $query->where('category_id', $current_category->id);
            }
        }

        $questions = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';

        $website_title = 'پرسش و پاسخ | ' . $brand;

        $seo = [
            'title' => 'پرسش و پاسخ',
            'description' => 'پرسش‌های کاربران و پاسخ‌های کارشناسی گروه رسانه‌ای صبح ساحل؛ سؤال خود را ثبت کنید',
            'type' => 'website',
            'url' => route('website.rtl.qa'),
        ];

        return view('website.rtl.qa', compact(
            'questions',
            'categories',
            'search',
            'current_category',
            'website_title',
            'seo'
        ));
    }

    public function show($id, $slug = null)
    {
        $question = Question::published()
            ->with(['category', 'published_answers'])
            ->findOrFail($id);

        $categories = QuestionCategory::active()
            ->orderBy('sort')
            ->orderBy('title')
            ->get();

        $brand = setting('general.fa_brand_name') ?? 'گروه رسانه‌ای صبح‌ساحل';

        $website_title = $question->title . ' | پرسش و پاسخ | ' . $brand;

        $seo = [
            'title' => $question->title,
            'description' => \Illuminate\Support\Str::limit(strip_tags($question->body), 200),
            'type' => 'article',
            'url' => $question->getUrl(),
        ];

        return view('website.rtl.qa_single', compact(
            'question',
            'categories',
            'website_title',
            'seo'
        ));
    }

    public function store(Request $request)
    {
        // Honeypot: real users never fill this hidden field
        if (filled($request->get('website'))) {
            return redirect()->route('website.rtl.qa');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:120',
            'email' => 'nullable|email|max:190',
            'phone' => 'nullable|string|max:20',
            'category_id' => 'required|exists:question_categories,id',
            'title' => 'required|string|max:255',
            'body' => 'required|string|max:5000',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ], [
            'name.required' => 'وارد کردن نام الزامی است',
            'email.email' => 'ایمیل وارد شده معتبر نیست',
            'category_id.required' => 'انتخاب دسته‌بندی الزامی است',
            'category_id.exists' => 'دسته‌بندی انتخاب شده معتبر نیست',
            'title.required' => 'وارد کردن عنوان پرسش الزامی است',
            'body.required' => 'وارد کردن متن پرسش الزامی است',
            'attachment.file' => 'فایل ضمیمه معتبر نیست',
            'attachment.mimes' => 'فرمت فایل ضمیمه باید PDF ،JPG یا PNG باشد',
            'attachment.max' => 'حجم فایل ضمیمه حداکثر ۲ مگابایت است',
        ]);

        $attachment_path = null;

        if ($request->hasFile('attachment')) {
            $attachment_path = $request->file('attachment')->store('qa/attachments', 'public');
        }

        Question::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'category_id' => $validated['category_id'],
            'title' => $validated['title'],
            'body' => $validated['body'],
            'attachment' => $attachment_path,
            'status' => Question::STATUS_PENDING,
            'is_featured' => false,
            'is_admin_created' => false,
            'user_id' => auth()->id(),
            'ip_address' => $request->ip(),
        ]);

        return redirect()
            ->back()
            ->with('qa_success', 'پرسش شما با موفقیت ثبت شد و پس از بررسی منتشر خواهد شد');
    }
}
