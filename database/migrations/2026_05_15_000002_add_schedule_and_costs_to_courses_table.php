<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->date('start_date')->nullable()->after('description');
            $table->date('end_date')->nullable()->after('start_date');
            $table->date('payment_start_date')->nullable()->after('end_date');
            $table->date('payment_end_date')->nullable()->after('payment_start_date');
            $table->decimal('minimum_payment', 10, 2)->default(0)->after('payment_end_date');
            $table->decimal('course_cost', 10, 2)->default(0)->after('minimum_payment');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn([
                'start_date',
                'end_date',
                'payment_start_date',
                'payment_end_date',
                'minimum_payment',
                'course_cost',
            ]);
        });
    }
};
