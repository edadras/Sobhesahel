<?php

namespace App\Livewire;

use App\Models\Contact;
use Jantinnerezo\LivewireAlert\LivewireAlert;
use Livewire\Component;

class ContactForm extends Component
{
    use LivewireAlert;

    public $name;
    public $email;
    public $text;

    protected $rules = [
        'name' => 'required',
        'email' => 'required|email',
        'text' => 'required',
    ];

    public function send()
    {
        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->alert('error', $e->validator->errors()->first(), [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه'
            ]);
            return;
        }

        $lang_id = (request()->has('lang') && request()->get('lang') == 'en') ? 2 : 1;

        $contact = Contact::create([
            'name' => $this->name,
            'email' => $this->email,
            'text' => $this->text,
            'ip_address' => request()->ip(),
            'lang_id' => $lang_id,
        ]);

        if ($contact instanceof Contact) {
            $this->reset(['name', 'email', 'text']);

            $this->alert('success', $lang_id == 1 ? 'پیام شما با موفقیت ثبت شد' : 'Your message has been successfully submitted', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه'
            ]);
        } else {
            $this->alert('error', $lang_id == 1 ? 'خطایی در ثبت پیام شما رخ داده' : 'An error occurred while submitting your message', [
                'toast' => false,
                'position' => 'center',
                'showConfirmButton' => true,
                'confirmButtonText' => 'باشه'
            ]);
        }
    }

    public function render()
    {
        return view('livewire.contact-form');
    }
}
