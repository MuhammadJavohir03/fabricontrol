<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * `miqdori` ustuni unsigned edi — bu esa ranglar sonini kamaytirishda
     * (masalan, -1) "Out of range value" xatosiga sabab bo'lardi.
     * Endi manfiy qiymatlar ham (qo'lda tuzatish/kamaytirish) yozilishi mumkin.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `ishlab_chiqarishlar` MODIFY `miqdori` INT NOT NULL DEFAULT 0');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE `ishlab_chiqarishlar` MODIFY `miqdori` INT UNSIGNED NOT NULL DEFAULT 0');
    }
};
