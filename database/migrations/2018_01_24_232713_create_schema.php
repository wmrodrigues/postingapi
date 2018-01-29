<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Console\Scheduling\Schedule;

class CreateSchema extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // defining all api schema
        Schema::create("users", function (Blueprint $table){
            $table->increments("id");
            $table->timestamps();
            $table->string("login", 50);
            $table->string("password", 256);
            $table->boolean("subscriber");
            $table->string("email", 100);
            $table->integer("balance");
        });

        Schema::create("post", function(Blueprint $table) {
            $table->bigIncrements("id"); // big ammount of data here
            $table->timestamps();
            $table->string("description", 1000);
            $table->bigInteger("userid");
            $table->smallInteger("posttype"); //this could be an image, a video or a simple text

            $table->foreign("userid")->references("id")->on("users");
        });

        Schema::create("comment", function(Blueprint $table) {
            $table->bigIncrements("id");
            $table->timestamps();
            $table->integer("userid");
            $table->bigInteger("postid");
            $table->string("description", 4000);
            $table->boolean("highlight");
            $table->dateTime("highlightexpiration");
            $table->integer("coins")->default(0);

            $table->foreign("userid")->references("id")->on("users");
            $table->foreign("postid")->references("id")->on("post");
        });

        Schema::create("transaction", function(Blueprint $table) {
            $table->bigIncrements("id");
            $table->timestamps();
            $table->integer("userid");
            $table->bigInteger("postid");
            $table->bigInteger("commentid"); // this is redundant, but it's gonna be useful to performance
            $table->integer("coins");
            $table->integer("parentid")->nullable(); // value retained by the system's transaction

            $table->foreign("userid")->references("id")->on("users");
            $table->foreign("postid")->references("id")->on("post");
            $table->foreign("parentid")->references("id")->on("transaction");
        });

        Schema::create("notification", function(Blueprint $table) {
            $table->bigIncrements("id");
            $table->timestamps();
            $table->string("description", 500);
            $table->integer("fromuserid");
            $table->integer("touserid");
            $table->dateTime("expiration");

            $table->foreign("fromuserid")->references("id")->on("users");
            $table->foreign("touserid")->references("id")->on("users");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // removing all api schema for any logical reasons
        Schema::dropIfExists("notification");
        Schema::dropIfExists("transaction");
        Schema::dropIfExists("comment");
        Schema::dropIfExists("post");
        Schema::dropIfExists("users");
    }
}
