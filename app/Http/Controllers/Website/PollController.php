<?php

namespace App\Http\Controllers\Website;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\Poll;
use App\Models\PollOption;
use App\Models\PollVote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PollController extends Controller
{
    public static function get($id)
    {
        $poll = Poll::with('options')->findOrFail($id);

        $poll->is_active = (bool)$poll->is_active;
        $poll->login_required = (bool)$poll->login_required;

        return $poll;
    }

    public function vote(Request $request)
    {
         $lang = $request->get('lang');

         $this->validate($request,[
            'option_id' => 'required'
         ],[
             'option_id.required' => ($lang == 'fa') ? 'لطفا گزینه مورد نظر را انتخاب کنید' : 'Please select option'
         ]);

         $option = PollOption::findOrFail($request->get('option_id'));

         $poll = $option->poll;

         if ($poll->login_required && !auth()->check()){
             return ResponseHelper::simple_response(false,($lang == 'fa') ? 'برای شرکت در این نظر سنجی ابتدا وارد حساب کاربری خود شوید' : 'To participate in this survey, please log in to your account first.');
         }

        $userIp = $request->ip();
        $userAgent = $request->userAgent();
        $userId = Auth::id();

        $existingVote = PollVote::where('poll_id', $poll->id)
            ->where(function ($query) use ($userIp, $userAgent, $userId) {
                $query->where('user_ip', $userIp)
                    ->where('user_agent', $userAgent);
                if ($userId) {
                    $query->orWhere('user_id', $userId);
                }
            })
            ->exists();

        if ($existingVote) {
            return ResponseHelper::simple_response(false, $lang == 'fa' ? 'شما قبلاً در این نظرسنجی شرکت کرده‌اید' : 'You have already participated in this survey.');
        }

        PollVote::create([
            'poll_id' => $poll->id,
            'poll_option_id' => $option->id,
            'user_id' => $userId,
            'user_ip' => $userIp,
            'user_agent' => $userAgent,
        ]);

        return ResponseHelper::basic_response(true, [
            'message' => ($lang == 'fa') ? 'رأی شما با موفقیت ثبت شد' : 'Your vote has been successfully submitted.',
            'poll_id' => $poll->id
        ]);
    }

    public function result(Request $request)
    {
        $this->validate($request,[
           'poll_id' => 'required|exists:polls,id'
        ]);

        $poll = Poll::with('options.votes')->findOrFail($request->get('poll_id'));

        // Calculate the total number of votes for the poll
        $totalVotes = $poll->options->sum(function ($option) {
            return $option->votes->count();
        });

        $votePercentages = [];

        foreach ($poll->options as $option) {
            $voteCount = $option->votes->count();
            $percentage = $totalVotes > 0 ? round(($voteCount / $totalVotes) * 100, 2) : 0;

            $votePercentages[$option->id] = $percentage . '%';
        }

        return ResponseHelper::basic_response(true, ['votes' => $votePercentages]);
    }
}
