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
        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained()
                  ->cascadeOnDelete();
            $table->string('filename');
            $table->string('original_name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
 
            // Adding an index for product_id and sort_order to optimize queries(optional but i am doing it)

            $table->index(['product_id', 'sort_order']);
        });
    }
 
    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};
