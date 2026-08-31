<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('braklar', function (Blueprint $table) {
            if (! Schema::hasColumn('braklar', 'chevar_id')) {
                $table->foreignId('chevar_id')->nullable()->after('product_id')
                    ->constrained('users')->nullOnDelete();
            }
            // Chevar pulidan ayiriladigan summa (brak dona × shu chevar stavkasi)
            if (! Schema::hasColumn('braklar', 'chevar_jarima')) {
                $table->decimal('chevar_jarima', 15, 2)->unsigned()->default(0)->after('summa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('braklar', function (Blueprint $table) {
            if (Schema::hasColumn('braklar', 'chevar_id')) {
                $table->dropConstrainedForeignId('chevar_id');
            }
            if (Schema::hasColumn('braklar', 'chevar_jarima')) {
                $table->dropColumn('chevar_jarima');
            }
        });
    }
};
