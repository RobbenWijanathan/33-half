<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('condition')->default('new')->index();
            $table->string('record_grade')->nullable();
            $table->string('sleeve_grade')->nullable();
            $table->string('currency', 3)->default('USD');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['condition', 'record_grade', 'sleeve_grade', 'currency']);
        });
    }
};
