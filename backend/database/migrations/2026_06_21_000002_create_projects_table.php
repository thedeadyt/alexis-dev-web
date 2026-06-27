<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('client');
            $table->string('category');
            $table->string('year', 4);
            $table->text('summary');
            $table->json('full_text');
            $table->json('tech');
            $table->json('rendered');
            $table->unsignedTinyInteger('sort_order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('projects'); }
};
