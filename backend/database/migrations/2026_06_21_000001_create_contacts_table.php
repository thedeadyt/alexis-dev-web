<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('type')->default('Site vitrine');
            $table->string('budget')->default('À définir');
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('contacts'); }
};
