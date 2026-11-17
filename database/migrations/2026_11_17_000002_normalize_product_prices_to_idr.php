<?php

use App\Support\Money;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $rate = Money::rate();

        DB::table('products')->where('currency', 'USD')->update([
            'price' => DB::raw('ROUND(price * '.(float) $rate.', 0)'),
            'currency' => 'IDR',
        ]);

        Schema::table('products', function (Blueprint $table) {
            $table->string('currency', 3)->default('IDR')->change();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->change();
        });
    }
};
