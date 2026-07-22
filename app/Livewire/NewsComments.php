<?php

namespace App\Livewire;

use App\Models\Comment;
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

    protected $rules = [
        'comment_name' => 'required|string|max:255',
        'comment_email' => 'required|email|max:255',
        'comment_text' => 'required|string|max:1000',
    ];

    public function mount($post)
    {
        $this->post = $post;
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

    public function submitComment()
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

        Comment::create([
            'news_id' => $this->post->id,
            'name' => $this->comment_name,
            'email' => $this->comment_email,
            'comment' => $this->comment_text,
            'status' => 'pending', // Adjust if needed,
            'lang_id' => 1
        ]);

        // Clear input fields after submission
        $this->reset(['comment_name', 'comment_email', 'comment_text']);

        // Refresh comments list
        $this->loadComments();

        // Emit event to show success alert
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
