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
        Schema::table('invoice_detail', function (Blueprint $table) {
            $table->decimal('TaxPer',10,2)->nullable()->after('Fare');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_detail', function (Blueprint $table) {
            $table->dropColumn('TaxPer');
        });
    }
};
