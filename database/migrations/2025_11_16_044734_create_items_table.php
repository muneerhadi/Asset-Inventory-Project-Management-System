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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            // Business identifiers
            $table->string('item_code')->unique();
            $table->string('tag_number')->nullable();

            $table->string('name');
            $table->text('description')->nullable();

            $table->foreignId('item_category_id')->constrained('item_categories');
            $table->foreignId('item_status_id')->constrained('item_statuses');

            $table->decimal('price', 15, 2)->nullable();
            $table->foreignId('currency_id')->nullable()->constrained('currencies');
            $table->decimal('depreciation_rate', 5, 2)->nullable();

            $table->date('purchase_date')->nullable();
            $table->string('voucher_number')->nullable();

            $table->string('location')->nullable();
            $table->string('sublocation')->nullable();
            $table->string('model')->nullable();

            // Each item can belong to at most one project
            $table->foreignId('project_id')->nullable()->constrained('projects');

            $table->text('remarks')->nullable();
            $table->string('image_path')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
