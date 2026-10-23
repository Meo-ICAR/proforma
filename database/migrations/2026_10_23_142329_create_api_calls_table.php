<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro delle chiamate verso servizi esterni (Mediafacile, Business Central...): mai token, chiavi o payload.
     */
    public function up(): void
    {
        Schema::create('api_calls', function (Blueprint $table) {
            $table->id();
            $table->string('service', 60)->index();
            $table->string('operation', 120)->nullable();
            $table->string('method', 10);
            $table->string('url', 1000);
            $table->unsignedSmallInteger('status')->nullable();
            $table->boolean('successful')->default(false)->index();
            $table->unsignedInteger('duration_ms')->nullable();
            $table->unsignedBigInteger('response_bytes')->nullable();
            $table->text('error')->nullable();
            $table->timestamp('called_at')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_calls');
    }
};
