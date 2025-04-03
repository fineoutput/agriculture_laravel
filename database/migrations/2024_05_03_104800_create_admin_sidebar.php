<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAdminSidebar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('admin_sidebar', function (Blueprint $table) {
            $table->id();
            $table->string('name', 1000);
            $table->string('url', 2000);
            $table->string('icon')->nullable();
            $table->integer('seq');
            $table->timestamps();
            $table->softDeletes(); // Add the softDeletes method to add the 'deleted_at' column
        });
        Schema::create('admin_sidebar2', function (Blueprint $table) {
            $table->id();
            $table->integer('main_id');
            $table->string('name', 1000);
            $table->string('url', 2000);
            $table->timestamps();
            $table->softDeletes(); // Add the softDeletes method to add the 'deleted_at' column
        });
        Schema::create('admin_teams', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 500);
            $table->string('password', 2000);
            $table->string('phone', 100)->nullable();
            $table->string('address', 2000)->nullable();
            $table->string('image', 1000);
            $table->integer('power');
            $table->string('services', 1000)->nullable();
            $table->string('ip', 100);
            $table->integer('added_by');
            $table->integer('is_active');
            $table->timestamps();
            $table->softDeletes(); // Add the softDeletes method to add the 'deleted_at' column
        });
        Schema::create('admin_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sitename', 100);
            $table->string('instagram_link', 500);
            $table->string('facebook_link', 2000);
            $table->string('youtube_link', 100);
            $table->string('phone', 2000)->nullable();
            $table->string('address', 1000)->nullable();
            $table->string('logo');
            $table->string('ip', 1000)->nullable();
            $table->timestamps();
            $table->softDeletes(); // Add the softDeletes method to add the 'deleted_at' column
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('sitename');
            $table->string('instagram_link');
            $table->string('facebook_link');
            $table->string('youtube_link');
            $table->string('phone')->nullable();;
            $table->string('address')->nullable();;
            $table->string('logo');
            $table->string('ip');
            $table->timestamps();
            $table->softDeletes(); // Add the softDeletes method to add the 'deleted_at' column
        });
        Schema::create('crm_settings', function (Blueprint $table) {
            $table->id();
            $table->string('sitename');
            $table->string('instagram_link');
            $table->string('facebook_link');
            $table->string('youtube_link');
            $table->string('phone')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('added')->nullable();
            $table->string('logo')->nullable(); // Make logo field nullable
            $table->string('ip');
            $table->timestamps();
            $table->softDeletes(); // Add the softDeletes method to add the 'deleted_at' column
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('admin_sidebar');
        Schema::dropIfExists('admin_sidebar2');
        Schema::dropIfExists('admin_teams');
        Schema::dropIfExists('crm_settings');
        
    }
}
