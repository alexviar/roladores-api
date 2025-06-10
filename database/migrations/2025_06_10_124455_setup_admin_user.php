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
        User::create([
            'name' => 'Admin',
            'email' => env('ADMIN_EMAIL'),
            'password' => Hash::make(env('ADMIN_PASSWORD')),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        User::where('email', env('ADMIN_EMAIL'))->delete();
    }
};
