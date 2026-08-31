<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ishlab_chiqarishlar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('miqdori'); // shu safar nechta dona tikilgani
            $table->decimal('tan_narxi_dona', 15, 2)->unsigned(); // shu paytdagi 1 dona tan narxi
            $table->date('sana');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ishlab_chiqarishlar');
    }
};