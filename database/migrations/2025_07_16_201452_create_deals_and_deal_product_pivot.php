<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDealsAndDealProductPivot extends Migration
{
    public function up()
    {
        Schema::create('deals', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('E.g. "Friday Special 24% Off"');
            $table->unsignedTinyInteger('discount_percent')->comment('e.g. 24 for 24%');
            $table->boolean('applies_to_all')->default(true);
           $table->dateTime('starts_at');
$table->dateTime('ends_at');
            $table->timestamps();
        });

        Schema::create('deal_product', function (Blueprint $table) {
            $table->foreignId('deal_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->primary(['deal_id', 'product_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('deal_product');
        Schema::dropIfExists('deals');
    }
}
