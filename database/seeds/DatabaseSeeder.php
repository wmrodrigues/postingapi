<?php

use Illuminate\Database\Seeder;
use App\Models\Users;
use Illuminate\Support\Facades\Crypt;
use App\Models\Post;
use App\Models\Enums\PostTypeEnum;
use App\Models\Comment;
use App\Models\Transaction;

class DatabaseSeeder extends Seeder {
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run() {
        $this->call('UsersTableSeeder');
        $this->command->info("users table seeded...");

        $this->call("PostTableSeeder");
        $this->command->info("post table seeded...");

        $this->call("CommentTableSeeder");
        $this->command->info("comment table seeded...");

        $this->call("TransactionTableSeeder");
        $this->command->info("transaction table seeded...");

        $this->command->info("seding succeded.");
    }
}

class UsersTableSeeder extends Seeder {
    public function run() {
        DB::table("transaction")->delete();
        DB::table("comment")->delete();
        DB::table("post")->delete();
        DB::table("users")->delete();

        Users::create(array("login" => "user1", "password" => Crypt::encrypt("pass@word1"), "subscriber" => true, "email" => "user1@email.com", "balance" => 450));
        Users::create(array("login" => "user2", "password" => Crypt::encrypt("pass@word1"), "subscriber" => true, "email" => "user2@email.com", "balance" => 275));
        Users::create(array("login" => "user3", "password" => Crypt::encrypt("pass@word1"), "subscriber" => true, "email" => "user3@email.com", "balance" => 700));
        Users::create(array("login" => "user4", "password" => Crypt::encrypt("pass@word1"), "subscriber" => false, "email" => "user4@email.com", "balance" => 0));
    }
}

class PostTableSeeder extends Seeder {
    public function run() {
        DB::table("post")->delete();
        Post::create(array("description" => "This is user1 first post.", "userid" => 1, "posttype" => PostTypeEnum::TEXT));
        Post::create(array("description" => "https://www.youtube.com/watch?v=Ox73cwResl8", "userid" => 1, "posttype" => PostTypeEnum::VIDEO));
        Post::create(array("description" => "http://schmoesknow.com/wp-content/uploads/2017/05/Wonder-Woman-Movie-Artwork.jpg", "userid" => 1, "posttype" => PostTypeEnum::IMAGE));

        Post::create(array("description" => "This is user2 first post.", "userid" => 2, "posttype" => PostTypeEnum::TEXT));
        Post::create(array("description" => "https://www.youtube.com/watch?v=8BAhwgjMvnM", "userid" => 2, "posttype" => PostTypeEnum::VIDEO));
        Post::create(array("description" => "https://pmcvariety.files.wordpress.com/2017/07/black-panther.jpg", "userid" => 2, "posttype" => PostTypeEnum::IMAGE));
    }
}

class CommentTableSeeder extends Seeder {
    public function run() {
        DB::table("comment")->delete();
        $now = new DateTime();
        $after = new DateTime();
        $after->add(new \DateInterval("PT10M"));

        Comment::create(array("userid" => 1, "postid" => 5, "description" => "Dude, I can't wait to see this movie!", "highlight" => false, "highlightexpiration" => $now));
        Comment::create(array("userid" => 1, "postid" => 6, "description" => "Amazing Black Panther photo man, you should see my Wonder Woman pic that I just posted.", "highlight" => false, "highlightexpiration" => $now));

        Comment::create(array("userid" => 2, "postid" => 1, "description" => "Hey man, welcome to the club, you're gonna have so much fun here!", "highlight" => false, "highlightexpiration" => $now));
        Comment::create(array("userid" => 2, "postid" => 2, "description" => "This video is awesome, lol the entire night...", "highlight" => false, "highlightexpiration" => $now));
        Comment::create(array("userid" => 2, "postid" => 3, "description" => "Ok, I have to say, Gal Gadot is such an incredible wonman.", "highlight" => false, "highlightexpiration" => $now));

        Comment::create(array("userid" => 3, "postid" => 3, "description" => "Man, how I love this woman, she has this smile that can make you so crazy.", "highlight" => false, "highlightexpiration" => $now));
        Comment::create(array("userid" => 1, "postid" => 3, "description" => "Yeah, I agree, she's so perfect, her voice is so soft that I can almost fall asleep.", "highlight" => false, "highlightexpiration" => $now));
        Comment::create(array("userid" => 2, "postid" => 3, "description" => "You're damn right! Gal Gadot was elected bealty queen and beyond all that, she's mom of two kids, so perfect.", "highlight" => false, "highlightexpiration" => $now));
        Comment::create(array("userid" => 1, "postid" => 3, "description" => "Ok, ok gentlemen! Should we open a new post just to talk about this amazing woman?", "highlight" => true, "highlightexpiration" => $after, "coins" => 10));
        Comment::create(array("userid" => 3, "postid" => 3, "description" => "Definitely!", "highlight" => false, "highlightexpiration" => $now));
        Comment::create(array("userid" => 2, "postid" => 3, "description" => "Yes!", "highlight" => false, "highlightexpiration" => $now));
    }
}

class TransactionTableSeeder extends Seeder {
    public function run() {
        DB::table("transaction")->delete();

        Transaction::create(array("userid" => 1, "postid" => 3, "commentid" => 9, "coins" => 10));
        Transaction::create(array("userid" => 1, "postid" => 3, "commentid" => 9, "coins" => 1, "parentid" => 1));
    }
}