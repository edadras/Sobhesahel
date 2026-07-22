<?php

namespace App\Livewire;

use App\Models\Comment;
use App\Services\CommentSpamGuard;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class NewsComments extends Component
{
    use LivewireAlert;


    public $post;
    public $comments;
    public $comment_name;
    public $comment_email;
    public $comment_text;

    // Anti-spam: honeypot field (must stay empty) and form render timestamp.
    public $comment_website;
    public $form_rendered_at;

    protected $rules = [
        'comment_name' => 'required|string|max:255',
        'comment_email' => 'required|email|max:255',
        'comment_text' => 'required|string|max:1000',
    ];

    protected $messages = [
        'comment_name.required' => 'لطفاً نام خود را وارد کنید.',
        'comment_name.max' => 'نام وارد شده بیش از حد طولانی است.',
        'comment_email.required' => 'لطفاً ایمیل خود را وارد کنید.',
        'comment_email.email' => 'ایمیل وارد شده معتبر نیست.',
        'comment_email.max' => 'ایمیل وارد شده بیش از حد طولانی است.',
        'comment_text.required' => 'لطفاً متن دیدگاه را وارد کنید.',
        'comment_text.max' => 'متن دیدگاه نباید بیشتر از ۱۰۰۰ کاراکتر باشد.',
    ];

    public function mount($post)
    {
        $this->post = $post;
        $this->form_rendered_at = time();
        $this->loadComments();
    }

    public function loadComments()
    {
//        $this->comments = Comment::where('news_id', $this->post->id)
//            ->where('status','verified')
//            ->latest()
//            ->get();

        $this->comments = [];
    }

    public function submitComment(CommentSpamGuard $spamGuard)
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->alert('error', $e->validator->errors()->first(),[
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه'
            ]);
            return;
        }

        $verdict = $spamGuard->inspect(
            (string) $this->comment_text,
            $this->comment_website,
            $this->form_rendered_at ? (int) $this->form_rendered_at : null,
            request()->ip()
        );

        // Honeypot hit: silently drop, but pretend everything worked.
        if ($verdict['verdict'] === CommentSpamGuard::DROP) {
            $this->reset(['comment_name', 'comment_email', 'comment_text', 'comment_website']);
            $this->showSuccessAlert();
            return;
        }

        // Too fast or rate limited: show a Persian error, do not store.
        if ($verdict['verdict'] === CommentSpamGuard::THROTTLE) {
            $this->alert('error', $verdict['message'],[
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه'
            ]);
            return;
        }

        $isSpam = $verdict['verdict'] === CommentSpamGuard::REJECT;

        Comment::create([
            'news_id' => $this->post->id,
            'name' => $this->comment_name,
            'email' => $this->comment_email,
            'comment' => $this->comment_text,
            'status' => $isSpam ? 'rejected' : 'pending',
            'spam_reason' => $isSpam ? $verdict['reason'] : null,
            'ip' => request()->ip(),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 512),
            'lang_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $spamGuard->registerAttempt(request()->ip());

        // Clear input fields after submission
        $this->reset(['comment_name', 'comment_email', 'comment_text', 'comment_website']);

        // Refresh comments list
        $this->loadComments();

        // Emit event to show success alert
        $this->showSuccessAlert();
    }

    protected function showSuccessAlert(): void
    {
        $this->alert('success', 'دیدگاه شما با موفقیت ثبت شد و پس از تأیید نمایش داده خواهد شد.',[
            'toast' => false,
            'position' => 'center',
            'showConfirmButton' => true,
            'confirmButtonText' => 'باشه'
        ]);
    }

    public function render()
    {
        return view('livewire.news-comments');
    }
}
