<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('nomi');
            $table->string('tel_nomer')->nullable();
            $table->date('kirish_muddati')->nullable(); // null = muddatsiz (masalan "Standart")
            $table->boolean('faol')->default(true);      // super_admin qo'lda bloklashi/ochishi mumkin
            $table->text('izoh')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
