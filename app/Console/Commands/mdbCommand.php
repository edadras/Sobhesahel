<?php

namespace App\Console\Commands;

use App\Models\Archive;
use App\Models\ArchiveCategory;
use App\Models\Author;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Contact;
use App\Models\Gallery;
use App\Models\News;
use App\Models\Note;
use App\Models\Podcast;
use App\Models\User;
use App\Models\Video;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use setasign\Fpdi\Fpdi;
use Spatie\Tags\Tag;
use function Livewire\of;

class mdbCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:mdb {start} {end}';

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
//        $this->migrate_users();
//
//        $this->migrate_categories();

//        $this->migrate_tags();

//        News::where('publish_at',null)->orderBy('id','DESC')->chunk(500,function ($posts){
//            foreach ($posts as $post){
//                echo "Proccessing post {$post->id}";
//
//                $post->update([
//                   'publish_at' => $post->created_at
//                ]);
//            }
//        });

//        dd(ArchiveCategory::all());

//        $this->migrate_old_pdf();
//        $this->migrate_news();
//        $this->migrate_notess();
//
//        $this->migrate_archive();
//        $this->migrate_comments_new();
//
//        $this->migrate_comments();
//
//        $this->migrate_photos();
//
        // $this->migrate_pdf();
//
//  s      $this->migrate_podcast();

//        $this->delete_dummy();
//            dd()
//        $this->migrate_videos();

//        $this->add_data_this_way();

//        $this->delete_unsync_posts();
    }


    public function migrate_old_pdf()
    {
        $parsed = [];

        $dick=[
            1 => 1,
            2 => 4,
            7 => 3,
            8 => 7 ,
            5 => 6,
            4 => 5,
            6 => 2
        ];

        // Step 1: Load all PDF file records
        $data = DB::connection('mysql2')->table('ssn_pdf_files')->get();

        // Step 2: Group by issue_id
        foreach ($data as $item) {
            $parsed[$item->issue_id]['files'][] = $item;

            if (!isset($parsed[$item->issue_id]['issue'])) {
                $parsed[$item->issue_id]['issue'] = DB::connection('mysql2')->table('ssn_pdf_issue')->find($item->issue_id);
            }
        }

        // Step 3: For each issue, sort by page and merge
        foreach ($parsed as $issue_id => &$group) {
            try {
                $issue = $group['issue'];
                $files = $group['files'];

                // Sort files by page number
                usort($files, fn($a, $b) => $a->page <=> $b->page);

                $pdf = new Fpdi();

                foreach ($files as $file) {
                    $path = storage_path("app/public/upload/pdf/{$file->filename}");

                    if (!file_exists($path)) {
                        logger()->warning("Missing PDF file: $path");
                        continue;
                    }

                    $pageCount = $pdf->setSourceFile($path);

                    for ($i = 1; $i <= $pageCount; $i++) {
                        $tpl = $pdf->importPage($i);
                        $size = $pdf->getTemplateSize($tpl);

                        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                        $pdf->useTemplate($tpl);
                    }
                }

                // Step 4: Save merged file
                $filename = "issue_{$issue->title_id}_{$issue->issue_no}.pdf";
                $outputPath = storage_path("app/public/upload/pdf/{$filename}");
                $pdf->Output('F', $outputPath);

                // Step 5: Add to parsed array
                $group['merged_path'] = $outputPath;


                $data_to_store = [
                    'title' => 'شماره'  .' ' . $issue->issue_no,
                    'user_id' => 14,
                    'image_large' => 'upload/pdf/' . $issue->image,
                    'lang_id' =>1,
                    'archive_file' => 'upload/pdf/' . $filename,
                    'is_published' => true,
                    'published_at' => Carbon::createFromTimestamp($issue->timestamp)->addHours(5),
                    'archive_date' => Carbon::createFromTimestamp($issue->timestamp)->addHours(5)->format('Y-m-d'),
                    'status' => 'published',
                ];

                $arch = Archive::updateOrCreate([
                    'category_id' => $dick[$issue->title_id],
                    'archive_number' => $issue->issue_no,
                ],
                    $data_to_store);

                echo  "archive $issue->issue_no added \n";
            }catch (\Exception $e){

            }
        }

//
//        return $parsed;
    }

    private function migrate_tags()
    {
        $this->alert('Start migrating Tags');

        // دریافت تمام تگ‌های قدیمی
        DB::connection('mysql2')->table('tags')->orderBy('id')->chunk(100, function ($tags) {
            foreach ($tags as $oldTag) {
                // بررسی اگر تگ با همین نام از قبل وجود دارد
                $existingTag = Tag::findFromString($oldTag->name, 'fa');

                if (!$existingTag) {
                    // ایجاد تگ جدید در سیستم Spatie
                    $tag = Tag::findOrCreate($oldTag->name, 'fa');
                    echo "Tag added: " . $tag->name . "\n";
                } else {
                    echo "Tag already exists: " . $existingTag->name . "\n";
                }
            }
        });

        $this->alert('Tags migration completed');
    }

    private function delete_unsync_posts()
    {
        $posts = Post::where('post_type', 'video')->get();

        foreach ($posts as $post) {
            $post->delete_post();
        }
    }

    private function add_data_this_way()
    {
        PostTranslation::orderBy('id')->chunk(100, function ($items) {
            $this->sync_data($items);
        });
    }

    private function sync_data($items)
    {
        foreach ($items as $item) {
            try {
                $post = Post::findOrFail($item->post_id);

                $post->update([
                    'post_type' => $item->post_type,
                    'lang_id' => $item->lang_id
                ]);

                echo $post->id . ' Done ' . PHP_EOL;
            } catch (\Exception $e) {
                $item->delete();
            }
        }
    }

    private function migrate_users()
    {
        $this->alert('start migrating users');

        $oldDB = DB::connection('mysql2')->table('users')->where('role_id','!=',null)->where('email','!=',null)->get();

        $users = [];

        foreach ($oldDB as $item) {
            $users[] = [
                'password' => Hash::make($item->username . '@#123'),
                'name' => $item->first_name . ' ' . $item->last_name,
                'email' => $item->email,
                'avatar' => $item->avatar == null ? null : ltrim($item->avatar, '/'),
            ];
        }

        foreach ($users as $user) {
            User::create($user);

            echo "One user added \n";
        }


        $oldDB = DB::connection('mysql2')->table('users')->where('role_id','!=',null)->where('email',null)->get();

        $users = [];

        foreach ($oldDB as $item) {
            $users[] = [
                'name' => $item->first_name . ' ' . $item->last_name,
                'nik_name' => $item->nik_name,
                'avatar' => $item->avatar == null ? null : ltrim($item->avatar, '/'),
            ];
        }

        foreach ($users as $user) {
            Author::create($user);

            echo "One Author added \n";
        }

//        $authors = DB::connection('mysql2')->table('ssn_authors')->get();
//
//        foreach ($authors as $item) {
//            $users[] = [
//                'username' => Str::random(10),
//                'first_name' => $item->author,
//                'role_id' => 2,
//                'avatar' => ($item->filename != null) ? '/upload/' . $item->filename : null
//            ];
//        }


    }

    private function migrate_comments()
    {
        $this->alert('start migrating comments');

        $oldDB = DB::connection('mysql2')->table('ssn_comments')->get();

        foreach ($oldDB as $comment) {
            if ($comment->status_id == 1) {
                $status = 'pending';
            }

            if ($comment->status_id == 2) {
                $status = 'rejected';
            }

            if ($comment->status_id == 3) {
                $status = 'verified';
            }

            try {
                $post_id = Post::where('post_code', $comment->item_id)->first()->id;
            } catch (\Exception $e) {
                continue;
            }

            try {
                $com = Comment::create([
                    'post_id' => $post_id,
                    'name' => $comment->name,
                    'email' => $comment->email,
                    'comment' => $comment->comment,
                    'status' => $status,
                    'created_at' => Carbon::createFromTimestamp($comment->timestamp),
                    'lang_id' => 1
                ]);
            } catch (\Exception $e) {

            }

            echo "Comment added " . $com->id . "\n";
        }
    }

    private function migrate_categories()
    {
        $this->alert('start migrating Categories');

        $oldDB = DB::connection('mysql2')->table('ssn_category')->get();

//        $creator = $this->findUserIdByUsername('sobhesahel');

        foreach ($oldDB as $item) {
            $cat = Category::create([
                'parent_id' => $item->parent_id,
                'title' => $item->fa_name,
                'en_title' => $item->en_name,
                'slug' => $item->slug,
                'en_slug' => $item->slug,
            ]);

            echo "Category added " . $cat->id . "\n";
        }
    }

    private function migrate_notess()
    {
        $this->alert('Start migrating News');
        $start = (int) $this->argument('start');
        $end = (int) $this->argument('end');

        $categories_new = Category::pluck('id', 'slug')->toArray();
        $usersMap = User::pluck('id', 'email')->toArray();
        $authorsMap = Author::pluck('id', 'name')->toArray();



        DB::connection('mysql2')->table('posts')
            ->where('post_type', 'note')
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

                    if (Note::where('id',$post->post_code)->count() != 0){
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

                    echo "Mapping Post ID {$post->id}: User ID = $userId, Author ID = " . ($authorId ?? 'NULL') . "\n";

                    $translation = DB::connection('mysql2')->table('post_translations')
                        ->where('post_id', $post->id)
                        ->first();

                    if (!$translation) {
                        echo "Skipping Post ID {$post->id} (No translation found)\n";
                        continue;
                    }

//                    $categoryIds = DB::connection('mysql2')->table('post_categories')
//                        ->where('post_id', $post->id)
//                        ->pluck('category_id')
//                        ->toArray();
//
//                    $categories = DB::connection('mysql2')->table('categories')
//                        ->whereIn('id', $categoryIds)
//                        ->pluck('slug')
//                        ->toArray();

                    $tagIds = DB::connection('mysql2')->table('post_tag')
                        ->where('post_id', $post->id)
                        ->pluck('tag_id')
                        ->toArray();

                    $tags = DB::connection('mysql2')->table('tags')
                        ->whereIn('id', $tagIds)
                        ->pluck('name')
                        ->toArray();

                    $data = [
                        'id' => $post->post_code,
                        'title' => $translation->title ?? 'بدون عنوان',
                        'sub_title' => $translation->subtitle ?? null,
                        'slug' => $translation->slug,
                        'user_id' => $userId,
                        'author_id' => $authorId,
                        'image_large' => $translation->image_large ?? null,
                        'image_medium' => $translation->image_medium ?? null,
                        'image_small' => $translation->image_thumbnail ?? null,
                        'meta_desc' => $translation->meta_desc ?? null,
                        'seo_title' => $translation->seo_title ?? null,
                        'body' => $translation->post_body ?? '',
                        'short_description' => $translation->short_description ?? null,
                        'lang_id' => $translation->lang_id,
                        'visits' => $post->visits,
                        'is_published' => $post->status === 'published',
                        'publish_at' => $post->posted_at,
                        'status' => $post->status,
                        'created_at' => $post->posted_at,
                        'updated_at' => $post->posted_at,
                    ];

                    $newsPost = Note::create($data);
                    echo "Post ID {$newsPost->id} migrated successfully.\n";

//                    if (!empty($categories)) {
//                        $categoryIdsMapped = array_filter(array_map(fn ($slug) => $categories_new[$slug] ?? null, $categories));
//                        $newsPost->categories()->sync($categoryIdsMapped);
//                        echo " - Categories added: " . implode(', ', $categories) . "\n";
//                    }

                    $tags = array_filter($tags, fn ($tag) => !empty(trim($tag)));

                    if (!empty($tags)) {
                        $tagObjects = array_map(fn ($tag) => Tag::findOrCreate($tag, 'fa'), $tags);
                        $newsPost->attachTags($tagObjects);
                        echo " - Tags added: " . implode(', ', $tags) . "\n";
                    }

//                    dd();
                }
            });


        $this->alert('News migration completed.');
    }
    private function migrate_news()
    {
        $this->alert('Start migrating News');
        $start = (int) $this->argument('start');
        $end = (int) $this->argument('end');

        $categories_new = Category::pluck('id', 'slug')->toArray();
        $usersMap = User::pluck('id', 'email')->toArray();
        $authorsMap = Author::pluck('id', 'name')->toArray();

        News::where('id','>',129683)->delete();

        echo "delete post \n";

        sleep(10);

        DB::connection('mysql2')->table('posts')
            ->where('id','>',130291)
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

                    if (News::where('id',$post->post_code)->count() != 0){
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

                    echo "Mapping Post ID {$post->id}: User ID = $userId, Author ID = " . ($authorId ?? 'NULL') . "\n";

                    $translation = DB::connection('mysql2')->table('post_translations')
                        ->where('post_id', $post->id)
                        ->first();

                    if (!$translation) {
                        echo "Skipping Post ID {$post->id} (No translation found)\n";
                        continue;
                    }

                    $categoryIds = DB::connection('mysql2')->table('post_categories')
                        ->where('post_id', $post->id)
                        ->pluck('category_id')
                        ->toArray();

                    $categories = DB::connection('mysql2')->table('categories')
                        ->whereIn('id', $categoryIds)
                        ->pluck('slug')
                        ->toArray();

                    $tagIds = DB::connection('mysql2')->table('post_tag')
                        ->where('post_id', $post->id)
                        ->pluck('tag_id')
                        ->toArray();

                    $tags = DB::connection('mysql2')->table('tags')
                        ->whereIn('id', $tagIds)
                        ->pluck('name')
                        ->toArray();

                    $data = [
                        'id' => $post->post_code,
                        'title' => $translation->title ?? 'بدون عنوان',
                        'sub_title' => $translation->subtitle ?? null,
                        'slug' => $translation->slug,
                        'user_id' => $userId,
                        'author_id' => $authorId,
                        'image_large' => $translation->image_large ?? null,
                        'image_medium' => $translation->image_medium ?? null,
                        'image_small' => $translation->image_thumbnail ?? null,
                        'meta_desc' => $translation->meta_desc ?? null,
                        'seo_title' => $translation->seo_title ?? null,
                        'body' => $translation->post_body ?? '',
                        'short_description' => $translation->short_description ?? null,
                        'lang_id' => $translation->lang_id,
                        'visits' => $post->visits,
                        'is_published' => $post->status === 'published',
                        'publish_at' => $post->posted_at,
                        'status' => $post->status,
                        'created_at' => $post->posted_at,
                        'updated_at' => $post->posted_at,
                    ];

                    $newsPost = News::create($data);
                    echo "Post ID {$newsPost->id} migrated successfully.\n";

                    if (!empty($categories)) {
                        $categoryIdsMapped = array_filter(array_map(fn ($slug) => $categories_new[$slug] ?? null, $categories));
                        $newsPost->categories()->sync($categoryIdsMapped);
                        echo " - Categories added: " . implode(', ', $categories) . "\n";
                    }

                    $tags = array_filter($tags, fn ($tag) => !empty(trim($tag)));

                    if (!empty($tags)) {
                        $tagObjects = array_map(fn ($tag) => Tag::findOrCreate($tag, 'fa'), $tags);
                        $newsPost->attachTags($tagObjects);
                        echo " - Tags added: " . implode(', ', $tags) . "\n";
                    }

//                    dd();
                }
            });


        $this->alert('News migration completed.');
    }
    private function migrate_galle()
    {
        $this->alert('Start migrating News');

        $categories_new = Category::pluck('id', 'slug')->toArray();
        $usersMap = User::pluck('id', 'email')->toArray();
        $authorsMap = Author::pluck('id', 'name')->toArray();



        DB::connection('mysql2')->table('posts')
            ->where('post_type', 'video')
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

                    if (Video::where('id',$post->post_code)->count() != 0){
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

                    echo "Mapping Post ID {$post->id}: User ID = $userId, Author ID = " . ($authorId ?? 'NULL') . "\n";

                    $translation = DB::connection('mysql2')->table('post_translations')
                        ->where('post_id', $post->id)
                        ->first();

                    if (!$translation) {
                        echo "Skipping Post ID {$post->id} (No translation found)\n";
                        continue;
                    }

//                    $categoryIds = DB::connection('mysql2')->table('post_categories')
//                        ->where('post_id', $post->id)
//                        ->pluck('category_id')
//                        ->toArray();
//
//                    $categories = DB::connection('mysql2')->table('categories')
//                        ->whereIn('id', $categoryIds)
//                        ->pluck('slug')
//                        ->toArray();

                    $tagIds = DB::connection('mysql2')->table('post_tag')
                        ->where('post_id', $post->id)
                        ->pluck('tag_id')
                        ->toArray();

                    $tags = DB::connection('mysql2')->table('tags')
                        ->whereIn('id', $tagIds)
                        ->pluck('name')
                        ->toArray();

                    try {
                        $video = DB::connection('mysql2')->table('post_data')->where('post_id', $post->id)->where('title','video')->firstOrFail();

                        $video_data = json_decode($video->data,true);

                        if (!isset($video_data['embed'])){
                            continue;
                        }
                    }catch (\Exception $e){
                        continue;
                    }

                    $data = [
                        'id' => $post->post_code,
                        'title' => $translation->title ?? 'بدون عنوان',
                        'sub_title' => $translation->subtitle ?? null,
                        'slug' => $translation->slug,
                        'user_id' => $userId,
                        'author_id' => $authorId,
                        'image_large' => $translation->image_large ?? null,
                        'image_medium' => $translation->image_medium ?? null,
                        'image_small' => $translation->image_thumbnail ?? null,
                        'meta_desc' => $translation->meta_desc ?? null,
                        'seo_title' => $translation->seo_title ?? null,
                        'body' => $translation->post_body ?? '',
                        'short_description' => $translation->short_description ?? null,
                        'lang_id' => $translation->lang_id,
                        'visits' => $post->visits,
                        'is_published' => $post->status === 'published',
                        'publish_at' => $post->posted_at,
                        'status' => $post->status,
                        'created_at' => $post->posted_at,
                        'updated_at' => $post->posted_at,
                        'embed' => $video_data['embed']
                    ];

                    $newsPost = Video::create($data);
                    echo "Post ID {$newsPost->id} migrated successfully.\n";

//                    if (!empty($categories)) {
//                        $categoryIdsMapped = array_filter(array_map(fn ($slug) => $categories_new[$slug] ?? null, $categories));
//                        $newsPost->categories()->sync($categoryIdsMapped);
//                        echo " - Categories added: " . implode(', ', $categories) . "\n";
//                    }

                    $tags = array_filter($tags, fn ($tag) => !empty(trim($tag)));

                    if (!empty($tags)) {
                        $tagObjects = array_map(fn ($tag) => Tag::findOrCreate($tag, 'fa'), $tags);
                        $newsPost->attachTags($tagObjects);
                        echo " - Tags added: " . implode(', ', $tags) . "\n";
                    }

//                    dd();
                }
            });


        $this->alert('News migration completed.');
    }
    private function migrate_archive()
    {
        $this->alert('Start migrating News');

        $categories_new = ArchiveCategory::pluck('id', 'slug')->toArray();
        $usersMap = User::pluck('id', 'email')->toArray();
        $authorsMap = Author::pluck('id', 'name')->toArray();



        DB::connection('mysql2')->table('posts')
            ->where('post_type', 'archive')
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

                    if (Archive::where('id',$post->post_code)->count() != 0){
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

                    echo "Mapping Post ID {$post->id}: User ID = $userId, Author ID = " . ($authorId ?? 'NULL') . "\n";

                    $translation = DB::connection('mysql2')->table('post_translations')
                        ->where('post_id', $post->id)
                        ->first();

                    if (!$translation) {
                        echo "Skipping Post ID {$post->id} (No translation found)\n";
                        continue;
                    }



                    try {
                        $archive_number = DB::connection('mysql2')->table('post_data')->where('post_id', $post->id)->where('title','number')->firstOrFail()->value;
                        $archive_date = DB::connection('mysql2')->table('post_data')->where('post_id', $post->id)->where('title','date')->firstOrFail()->value;
                        $archive_category = DB::connection('mysql2')->table('post_data')->where('post_id', $post->id)->where('title','category')->firstOrFail()->value;
                        $archive_file = DB::connection('mysql2')->table('post_data')->where('post_id', $post->id)->where('title','archive')->firstOrFail()->data;
                        $archive_file = json_decode($archive_file,true)['file'];


                        $archive_category = $categories_new[$archive_category];
                    }catch (\Exception $e){
                        continue;
                    }

                    $data = [
                        'id' => $post->post_code,
                        'title' => $translation->title ?? 'بدون عنوان',
//                        'sub_title' => $translation->subtitle ?? null,
//                        'slug' => $translation->slug,
                        'user_id' => $userId,
//                        'author_id' => $authorId,
                        'image_large' => $translation->image_large ?? null,
                        'image_medium' => $translation->image_medium ?? null,
                        'image_small' => $translation->image_thumbnail ?? null,
                        'meta_desc' => $translation->meta_desc ?? null,
                        'seo_title' => $translation->seo_title ?? null,
                        'body' => $translation->post_body ?? '',
                        'short_description' => $translation->short_description ?? null,
                        'lang_id' => $translation->lang_id,
                        'visits' => $post->visits,
                        'is_published' => $post->status === 'published',
                        'publish_at' => $post->posted_at,
                        'status' => $post->status,
                        'created_at' => $post->posted_at,
                        'updated_at' => $post->posted_at,
                        'archive_date' => $archive_date,
                        'category_id' => $archive_category,
                        'archive_number' => $archive_number,
                        'archive_file' => $archive_file
                    ];

                    $newsPost = Archive::create($data);
                    echo "Post ID {$newsPost->id} migrated successfully.\n";

//                    if (!empty($categories)) {
//                        $categoryIdsMapped = array_filter(array_map(fn ($slug) => $categories_new[$slug] ?? null, $categories));
//                        $newsPost->categories()->sync($categoryIdsMapped);
//                        echo " - Categories added: " . implode(', ', $categories) . "\n";
//                    }

//                    $tags = array_filter($tags, fn ($tag) => !empty(trim($tag)));
//
//                    if (!empty($tags)) {
//                        $tagObjects = array_map(fn ($tag) => Tag::findOrCreate($tag, 'fa'), $tags);
//                        $newsPost->attachTags($tagObjects);
//                        echo " - Tags added: " . implode(', ', $tags) . "\n";
//                    }

//                    dd();
                }
            });


        $this->alert('News migration completed.');
    }
    private function migrate_podcast_new()
    {
        $this->alert('Start migrating News');

        $categories_new = ArchiveCategory::pluck('id', 'slug')->toArray();
        $usersMap = User::pluck('id', 'email')->toArray();
        $authorsMap = Author::pluck('id', 'name')->toArray();



        DB::connection('mysql2')->table('posts')
            ->where('post_type', 'podcast')
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

                    if (Podcast::where('id',$post->post_code)->count() != 0){
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

                    echo "Mapping Post ID {$post->id}: User ID = $userId, Author ID = " . ($authorId ?? 'NULL') . "\n";

                    $translation = DB::connection('mysql2')->table('post_translations')
                        ->where('post_id', $post->id)
                        ->first();

                    if (!$translation) {
                        echo "Skipping Post ID {$post->id} (No translation found)\n";
                        continue;
                    }



                    try {
                        $archive_file = DB::connection('mysql2')->table('post_data')->where('post_id', $post->id)->where('title','podcast')->firstOrFail()->data;
                        $archive_file = json_decode($archive_file,true)['podcast'];


//                        $archive_category = $categories_new[$archive_category];
                    }catch (\Exception $e){
                        continue;
                    }

                    $data = [
                        'id' => $post->post_code,
                        'title' => $translation->title ?? 'بدون عنوان',
//                        'sub_title' => $translation->subtitle ?? null,
                        'slug' => $translation->slug,
                        'user_id' => $userId,
                        'author_id' => $authorId,
                        'image_large' => $translation->image_large ?? null,
                        'image_medium' => $translation->image_medium ?? null,
                        'image_small' => $translation->image_thumbnail ?? null,
                        'meta_desc' => $translation->meta_desc ?? null,
                        'seo_title' => $translation->seo_title ?? null,
                        'body' => $translation->post_body ?? '',
                        'short_description' => $translation->short_description ?? null,
                        'lang_id' => $translation->lang_id,
                        'visits' => $post->visits,
                        'is_published' => $post->status === 'published',
                        'publish_at' => $post->posted_at,
                        'status' => $post->status,
                        'created_at' => $post->posted_at,
                        'updated_at' => $post->posted_at,
                        'attachments' => [$archive_file]
                    ];

                    $newsPost = Podcast::create($data);
                    echo "Post ID {$newsPost->id} migrated successfully.\n";

//                    if (!empty($categories)) {
//                        $categoryIdsMapped = array_filter(array_map(fn ($slug) => $categories_new[$slug] ?? null, $categories));
//                        $newsPost->categories()->sync($categoryIdsMapped);
//                        echo " - Categories added: " . implode(', ', $categories) . "\n";
//                    }

//                    $tags = array_filter($tags, fn ($tag) => !empty(trim($tag)));
//
//                    if (!empty($tags)) {
//                        $tagObjects = array_map(fn ($tag) => Tag::findOrCreate($tag, 'fa'), $tags);
//                        $newsPost->attachTags($tagObjects);
//                        echo " - Tags added: " . implode(', ', $tags) . "\n";
//                    }

//                    dd();
                }
            });


        $this->alert('News migration completed.');
    }
    private function migrate_comments_new()
    {
        $this->alert('Start migrating News');
//
//        $categories_new = ArchiveCategory::pluck('id', 'slug')->toArray();
//        $usersMap = User::pluck('id', 'email')->toArray();
//        $authorsMap = Author::pluck('id', 'name')->toArray();
//
        Comment::where('id','>',0)->delete();

        DB::connection('mysql2')->table('comments')
            ->where('lang_id',1)
            ->orderBy('id')
            ->chunk(500, function ($comments) {
                foreach ($comments as $comment){

                    $post = DB::connection('mysql2')->table('posts')->where('id',$comment->post_id)->first();

                    if ($post == null){
                        continue;
                    }

                    $data = [
                        'user_id' => $comment->user_id,
                        'reply_to' => $comment->reply_to,
                        'name' => $comment->name,
                        'email' => $comment->email,
                        'comment' => $comment->comment,
                        'status' => $comment->status,
                        'lang_id' => $comment->lang_id,
                        'created_at' => $comment->created_at,
                        'updated_at' => $comment->updated_at,
                    ];

                    if ($post->post_type == 'news'){
                        $data['news_id'] = $post->post_code;
                    }else{
                        $data['note_id'] = $post->post_code;
                    }

                    Comment::create($data);

                    echo 'comment created' . PHP_EOL;
                }
            });


        $this->alert('News migration completed.');
    }

    private function migrate_contact(){
        $data = DB::connection('mysql2')->table('contacts')->orderBy('id')->chunk(500, function ($contacts){
            foreach ($contacts as $contact){
                Contact::create(json_decode(json_encode($contact),true));
            }
        });
    }

    private function migrate_single_news($item)
    {
        dd($item);

        $post_translation = DB::connection('mysql2')->table('post_translations')->where('post_id', $item->id)->get();

        $categories =

        $post_type = ($post_type_db == 0) ? 'news' : 'note';

        $post = Post::create([
            'user_id' => $this->findPostUserId($item),
//                'status' => ($item->published) ? 'published' : 'draft',
            'status' => 'published',
            'visits' => $item->visits,
            'unique_visits' => $item->visits,
            'post_code' => $item->id,
            'post_type' => $post_type,
            'lang_id' => 1,
            'posted_at' => Carbon::createFromTimestamp($item->timestamp),
        ]);

        if ($item->thumbnail != null && $item->thumbnail != '') {
            $image_large = 'upload/cover/' . $item->thumbnail;
        } else {
            $image_large = null;
        }

        try {
            $post->PostTranslation()->create([
                'slug' => $this->getSlug($item->title2),
                'title' => $item->title2,
                'subtitle' => $item->title1,
                'short_description' => '<p>' . strip_tags($item->brief) . '</p>',
                'post_body' => $item->description,
                'image_large' => $image_large,
                'lang_id' => 1,
                'post_type' => $post_type
            ]);

            $tags = $this->getTagsArray($item->keywords);

            Tag::StoreTags(implode(',', $tags), 1, $post);

            if ($post_type == 'news') {
                $cats = $this->getCategoriesArray($item);

                $post->categories()->sync($cats);
            }

        } catch (\Exception $e) {
            $post->delete();
        }
        echo "One $post_type added" . $post->id . "\n";
    }

    private function migrate_photos()
    {
        $this->alert('start migrating Images');

//        $oldDB = DB::connection('mysql2')->table('ssn_news')->get();
        $oldDB = DB::connection('mysql2')->table('ssn_photo')->where('id', '>', 378)->get();

        foreach ($oldDB as $item) {
            $post = Post::create([
                'user_id' => $this->findPostUserId($item),
//                'status' => ($item->published) ? 'published' : 'draft',
                'status' => 'published',
                'visits' => $item->visits,
                'unique_visits' => $item->visits,
                'post_code' => $item->id,
                'post_type' => 'image',
                'lang_id' => 1,
                'posted_at' => Carbon::createFromTimestamp($item->timestamp),
            ]);

            if ($item->thumbnail != null && $item->thumbnail != '') {
                $image_large = 'upload/cover/' . $item->thumbnail;
            } else {
                $image_large = null;
            }

            $post->PostTranslation()->create([
                'slug' => $this->getSlug($item->title),
                'title' => $item->title,
//                'subtitle' => $item->title1,
                'short_description' => '<p>' . strip_tags($item->brief) . '</p>',
//                'post_body' => $item->description,
                'image_large' => $image_large,
                'lang_id' => 1,
                'post_type' => 'image'
            ]);

            $images = DB::connection('mysql2')->table('ssn_photo_images')->where('photo_id', $item->id)->get();

            $data = [];

            foreach ($images as $image) {
                $data[] = 'upload/photo/' . $image->filename;
            }

            $post->PostData()->updateOrCreate(['title' => 'gallery'], ['data' => $data]);

            echo "One image added " . $post->id . "\n";
        }
    }

    private function migrate_videos()
    {
        $this->alert('start migrating videos');

//        $oldDB = DB::connection('mysql2')->table('ssn_news')->get();
        $oldDB = DB::connection('mysql2')->table('ssn_video')->where('id', '>', 30)->get();

        foreach ($oldDB as $item) {
            $post = Post::create([
                'user_id' => $this->findPostUserId($item),
//                'status' => ($item->published) ? 'published' : 'draft',
                'status' => 'published',
                'visits' => $item->visits,
                'unique_visits' => $item->visits,
                'post_code' => $item->id,
                'post_type' => 'video',
                'lang_id' => 1,
                'posted_at' => Carbon::createFromTimestamp($item->timestamp),
            ]);

            if ($item->thumbnail != null && $item->thumbnail != '') {
                $image_large = 'upload/cover/' . $item->thumbnail;
            } else {
                $image_large = null;
            }

            $post->PostTranslation()->create([
                'slug' => $this->getSlug($item->title),
                'title' => $item->title,
//                'subtitle' => $item->title1,
                'short_description' => '<p>' . strip_tags($item->brief) . '</p>',
//                'post_body' => $item->description,
                'image_large' => $image_large,
                'lang_id' => 1,
                'post_type' => 'video'
            ]);


            $data = [
                'embed' => $item->url
            ];

            $post->PostData()->updateOrCreate(['title' => 'video'], ['data' => $data]);

            echo "One video added " . $post->post_code . "\n";
        }
    }

    public function delete_dummy()
    {
        Post::orderBy('id')
            ->whereDoesntHave('PostTranslation')
            ->delete();
    }

    private function migrate_pdf()
    {
        $this->alert('start migrating pdfs');

//        $oldDB = DB::connection('mysql2')->table('ssn_news')->get();
        $oldDB = DB::connection('mysql2')->table('ssn_pdf_issue')->where('full_page_link', '!=', null)
            ->where('image', '!=', null)->where('id', '>', 3686)->get();

        foreach ($oldDB as $item) {
            if (!str_contains($item->full_page_link, 'https://sobhesahel.com')) {
                continue;
            }

            try {
                $title_seo = DB::connection('mysql2')->table('ssn_pdf_titles')->find($item->title_id)->seo;
            } catch (\Exception $e) {
                continue;
            }

            $post = Post::create([
                'user_id' => $this->findPostUserId($item),
//                'status' => ($item->published) ? 'published' : 'draft',
                'status' => 'published',
                'visits' => 0,
                'unique_visits' => 0,
                'post_code' => $item->id,
                'lang_id' => 1,
                'post_type' => 'archive',
                'posted_at' => Carbon::createFromTimestamp($item->timestamp),
            ]);

            $image_large = 'upload/pdf/' . $item->image;

            $title = 'شماره' . ' ' . $item->issue_no;

            $post->PostTranslation()->create([
                'slug' => $this->getSlug($title),
                'title' => $title,
//                'subtitle' => $item->title1,
//                'short_description' => '<p>' . strip_tags($item->brief) . '</p>',
//                'post_body' => $item->description,
                'image_large' => $image_large,
                'lang_id' => 1,
                'post_type' => 'archive'
            ]);


            $post->PostData()->updateOrCreate(['title' => 'category'], ['value' => $title_seo]);

            $post->PostData()->updateOrCreate(['title' => 'date'], ['value' => Carbon::createFromTimestamp($item->timestamp)->format('Y/m/d')]);

            $post->PostData()->updateOrCreate(['title' => 'number'], ['value' => $item->issue_no]);

            $post->PostData()->updateOrCreate(['title' => 'archive'], ['data' => [
                'file' => str_replace('https://sobhesahel.com/', '', $item->full_page_link)
            ]]);

            echo "One archive added " . $post->id . "\n";
        }
    }

    private function migrate_podcast()
    {
        $this->alert('start migrating podcast');

//        $oldDB = DB::connection('mysql2')->table('ssn_news')->get();
        $oldDB = DB::connection('mysql2')->table('ssn_podcast')->get();

        foreach ($oldDB as $item) {
            $post = Post::create([
                'user_id' => $this->findPostUserId($item),
//                'status' => ($item->published) ? 'published' : 'draft',
                'status' => 'published',
                'visits' => $item->view,
                'unique_visits' => $item->view,
                'post_code' => $item->id,
                'posted_at' => Carbon::createFromTimestamp($item->timestamp),
            ]);

            $title = 'شماره' . ' ' . $item->issue;

            $post->PostTranslation()->create([
                'slug' => $this->getSlug($title),
                'title' => $title,
//                'subtitle' => $item->title1,
//                'short_description' => '<p>' . strip_tags($item->brief) . '</p>',
                'post_body' => $item->description,
//                'image_large' => $image_large,
                'lang_id' => 1,
                'post_type' => 'podcast'
            ]);

            $data = [
                'podcast' => 'upload/podcast/' . $item->filename,
                'duration' => Mp3Helper::getMP3Duration(public_path('upload/podcast/' . $item->filename))
            ];

            $post->PostData()->updateOrCreate(['title' => 'podcast'], ['data' => $data]);

            echo "One podcast added " . $post->id . "\n";
        }
    }


//    -------

    private function findUserIdByUsername($username)
    {
        return User::find_by_username($username)?->id;
    }

    private function findPostUserId($post)
    {
        try {
            if (!isset($post->author_id) || $post->author_id == null) {
                $username = DB::connection('mysql2')->table('ssn_acp_users')->find($post->user_id)->username;

                $id = User::find_by_username($username)?->id;
            } else {
                $name = DB::connection('mysql2')->table('ssn_authors')->find($post->author_id)->author;

                $id = User::where('first_name', $name)->first()?->id;
            }

            return (isset($id)) ? $id : $this->findUserIdByUsername('sobhesahel');
        } catch (\Exception $e) {
            return $this->findUserIdByUsername('sobhesahel');
        }
    }

    private function createSlug($title)
    {
        // Normalize the title to decompose any special Unicode characters
        $title = mb_strtolower(trim($title), 'UTF-8');

        // Remove special characters, allow Persian (and alphanumeric) characters, underscores, and spaces
        $title = preg_replace('/[^\w\s\x{0600}-\x{06FF}]/u', '', $title);

        // Replace multiple spaces with a single space
        $title = preg_replace('/\s+/', ' ', $title);

        // Replace spaces with underscores
        $slug = str_replace(' ', '_', $title);

        return $slug;
    }

    private function getSlug($title)
    {
        return $this->createSlug($title);

//        while (true) {
//            if (PostTranslation::where('slug', $slug)->count() == 0) {
//                break;
//            }
//
//            $slug .= Str::random(1);
//        }
//
//        return $slug;
    }

    private function getTagsArray($tags)
    {
        return array_map('trim', explode("\n", $tags));
    }

    private function getCategoriesArray($post)
    {
        $oldDB = DB::connection('mysql2')->table('ssn_news_cat')->where('news_id', $post->id)->get();

        $cats = [];

        foreach ($oldDB as $cat_id) {
            try {
                $cat_db = DB::connection('mysql2')->table('ssn_category')->find($cat_id->cat_id);

                $new_cat = Category::where('slug', $cat_db->seo)->firstOrFail();

                $cats[] = $new_cat->id;
            } catch (\Exception $e) {

            }
        }

        return $cats;
    }
}
