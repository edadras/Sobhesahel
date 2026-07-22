<?php

namespace App\Console\Commands;

use App\Models\Author;
use App\Models\Category;
use App\Models\News;
use App\Models\Note;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Spatie\Tags\Tag;

class syncAuthor extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->migrate_news();
    }

    private function migrate_news()
    {
        $this->alert('Start migrating News');
//        $start = (int) $this->argument('start');
//        $end = (int) $this->argument('end');

        $categories_new = Category::pluck('id', 'slug')->toArray();
        $usersMap = User::pluck('id', 'email')->toArray();
        $authorsMap = Author::pluck('id', 'name')->toArray();



        DB::connection('mysql2')->table('posts')
            ->where('post_type', 'news')
            ->where('lang_id',1)
            ->orderBy('id','DESC')
            ->chunk(500, function ($posts) use ($categories_new, $usersMap, $authorsMap) {
                foreach ($posts as $post) {
                    echo "Processing Post ID: {$post->id}\n";

                    $oldUser = DB::connection('mysql2')->table('users')->where('id', $post->user_id)->first();

                    if (!$oldUser) {
                        echo "Skipping Post ID {$post->id} (User not found)\n";
                        continue;
                    }

                    try {
                        $news = News::findOrFail($post->post_code);
                    }catch (\Exception $e){
                        echo "Skipping Post Not Found  \n";

                        continue;
                    }

                    $userId = 1;
                    $authorId = null;

                    $fullName = $oldUser->first_name . ' ' . $oldUser->last_name;

                    if ($oldUser->email && isset($usersMap[$oldUser->email])) {
                        $userId = $usersMap[$oldUser->email];
                    } elseif (isset($authorsMap[$fullName])) {
                        $authorId = $authorsMap[$fullName];
                    }

                    if ($news->user_id == $userId && $news->author_id == $authorId){
                        echo "Post is OK {$news->id} \n";

                    }else{
                        echo "UPDATED {$post->id} \n";
                        $this->alert('Updated ' . $news->id);
                        $news->update([
                            'user_id' => $userId,
                            'author_id' => $authorId,
                        ]);

                    }

                }
            });


        $this->alert('News migration completed.');
    }
}
