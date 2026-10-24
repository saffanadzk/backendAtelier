<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('application_status')->default('none')->after('role'); // none, pending, approved, rejected
            $table->string('application_field')->nullable()->after('application_status');
            $table->string('application_portfolio')->nullable()->after('application_field');
            $table->text('application_pitch')->nullable()->after('application_portfolio');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'application_status',
                'application_field',
                'application_portfolio',
                'application_pitch',
            ]);
        });
    }
};
