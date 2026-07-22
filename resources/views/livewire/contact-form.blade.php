<div>
    <form wire:submit.prevent="send" class="row g-3">
        <div class="col-md-6">
            <label for="inpt01" class="form-label">نام و نام خانوادگی</label>
            <input wire:model="name" type="text" class="form-control" id="inpt01">
            @error('name') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-6">
            <label for="inpt02" class="form-label">ایمیل</label>
            <input wire:model="email" type="email" class="form-control" id="inpt02">
            @error('email') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-12">
            <label for="inpt03" class="form-label">متن</label>
            <textarea wire:model="text" class="form-control" id="inpt03" rows="3"></textarea>
            @error('text') <span class="text-danger">{{ $message }}</span> @enderror
        </div>
        <div class="col-12">
            <button type="submit" class="btn transitionCls">ارسال</button>
        </div>
    </form>
</div>
