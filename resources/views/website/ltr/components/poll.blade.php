<div class="sideBox" id="poll" data-lang="en">
    <div class="head">
        <span></span>
        <p>Survey</p>
    </div>
    <div class="sideSurvey">
        <div class="sideQues text-start">
            {{ $poll['question'] }}
        </div>
        <form>
            @foreach($poll['options'] as $item)
                <div class="form-check mb-2">
                    <input v-model="option" value="{{ $item['id'] }}" class="form-check-input" type="radio" name="vote"
                           id="option-{{ $item['id'] }}">
                    <label class="form-check-label" for="option-{{ $item['id'] }}">
                        {{ $item['option_text'] }}

                        <span v-if="result != null">
                            <span style="color:darkgreen" v-text="'(' + result[{{ $item['id'] }}]  + ')'"></span>
                        </span>
                    </label>
                </div>
            @endforeach
            <div class="sidSrvryBtn">
                <button @click="send_vote" type="button" class="btn transitionCls">
                    Submit
                </button>
                <button @click="get_result({{$poll['id']}})" type="button" class="btn transitionCls">View results</button>
            </div>
        </form>
    </div>
</div>


