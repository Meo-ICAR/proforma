<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
// database/migrations/[timestamp]_create_tipoprodotto_table.php

public function up()
{
    Schema::create('tipoprodotto', function (Blueprint $table) {
        $table->string('tipo_prodotto', 191)->primary()->comment('Tipologia di prodotto finanziario');
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_prodottos');
    }
};
