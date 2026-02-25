<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
  public function up(): void
{
    Schema::table('transactions', function (Blueprint $table) {
        // Add the username column. We make it nullable just in case 
        // you have old data in the database from before we added this!
        $table->string('username')->nullable()->after('id');
    });
}

public function down(): void
{
    Schema::table('transactions', function (Blueprint $table) {
        $table->dropColumn('username');
    });
}
};
