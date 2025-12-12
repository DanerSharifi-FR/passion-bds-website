<?php

declare(strict_types=1);

use App\Models\Allo;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Crée la table des slots d'allos.
     *
     * Statuts possibles:
     * - available : slot libre, réservable par un étudiant
     * - booked    : slot réservé
     * - blocked   : slot bloqué (maintenance / annulation globale)
     */
    public function up(): void
    {
        Schema::create('allo_slots', function (Blueprint $table): void {
            $table->id();

            $table->foreignIdFor(Allo::class, 'allo_id')
                ->constrained('allos')
                ->onDelete('cascade');

            $table->timestamp('slot_start_at');
            $table->timestamp('slot_end_at');

            // On limite à 20 caractères comme dans le dump SQL.
            $table->string('status', 20);

            $table->timestamp('created_at')->useCurrent();

            // Un slot par allo et par heure de début.
            $table->unique(['allo_id', 'slot_start_at'], 'uq_allo_slots_allo_start');
        });

        // Check constraint pour s'assurer qu'on reste dans nos 3 valeurs.
        DB::statement("
            ALTER TABLE allo_slots
            ADD CONSTRAINT chk_allo_slots_status_valid
            CHECK (status IN ('available', 'booked', 'blocked'))
        ");
    }

    /**
     * Drop de la table.
     */
    public function down(): void
    {
        Schema::dropIfExists('allo_slots');
    }
};
