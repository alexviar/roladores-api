<?php

use App\Models\Rolador;
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
        Schema::table('rental_periods', function (Blueprint $table) {
            $table->dropForeign(['rolador_id']);
            $table->unsignedBigInteger('rolador_id')->nullable()->change();
            $table->foreign('rolador_id')
                ->references('id')
                ->on('roladors')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rental_periods', function (Blueprint $table) {
            $table->dropForeign(['rolador_id']);
            $table->unsignedBigInteger('rolador_id')->nullable(false)->change();
            $table->foreign('rolador_id')
                ->references('id')
                ->on('roladors');
        });
    }
};
