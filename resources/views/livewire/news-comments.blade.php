<div class="newsAddCmnt">
    <div class="head">
        <div>
            <strong>دیدگاه ها</strong>
            <small>({{ count($comments) }})</small>
        </div>
        <button wire:click="submitComment" class="btn transitionCls">افزودن دیدگاه</button>
    </div>

    <div class="body">
        <div>
            <img src="/asset/img/user05.png" alt="img">
        </div>
        <span>
            <input wire:model="comment_name" type="text" class="form-control" placeholder="نام">
            @error('comment_name') <small class="text-danger">{{ $message }}</small> @enderror

            <input wire:model="comment_email" type="email" class="form-control" placeholder="ایمیل">
            @error('comment_email') <small class="text-danger">{{ $message }}</small> @enderror
        </span>

        <textarea wire:model="comment_text" class="form-control" placeholder="دیدگاه خود را بنویسید..." rows="3"></textarea>
        @error('comment_text') <small class="text-danger">{{ $message }}</small> @enderror

        {{-- Honeypot: hidden from humans, bots fill it and get silently dropped --}}
        <div style="position:absolute;left:-9999px;top:-9999px;height:0;width:0;overflow:hidden;" aria-hidden="true">
            <input wire:model="comment_website" type="text" name="comment_website" tabindex="-1" autocomplete="off">
        </div>
    </div>

    @foreach($comments as $comment)
        <div class="newsCmmnts">
            <div class="newsCmntHed">
                <div class="newsCmntUsr">
                    <div class="newsUsrImg">
                        <img src="/asset/img/user05.png" alt="img">
                    </div>
                    <div class="text-end">
                        <strong>{{ $comment->name }}</strong>
                        <i>{{\Carbon\Carbon::parse( $comment->created_at)->diffForHumans() }}</i>
                    </div>
                </div>
                <span class="icon-Reply-1"></span>
            </div>
            <div class="newsCmntTxt text-end">
                {{ $comment->comment }}
            </div>
        </div>
    @endforeach
</div>
