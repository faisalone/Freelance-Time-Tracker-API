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
        Schema::table('clients', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('contact_person');
            $table->text('address')->nullable()->after('phone');
            $table->decimal('hourly_rate', 8, 2)->nullable()->after('address');
            $table->enum('status', ['active', 'inactive'])->default('active')->after('hourly_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function (Blueprint $table) {
            $table->dropColumn(['phone', 'address', 'hourly_rate', 'status']);
        });
    }
};
