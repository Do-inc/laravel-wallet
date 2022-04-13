<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            if(config("wallet.uuid")) {
                $table->uuidMorphs("holder");
            }
            else {
                $table->morphs("holder");
            }

            $table->string("name");
            $table->longText("metadata")->nullable();
            $table->string("balance")->default("0");
            $table->unsignedSmallInteger("precision")->default(config("wallet.precision.global"));

            $table->timestamps();
            $table->unique(['holder_type', 'holder_id']);
        });
    }
};
