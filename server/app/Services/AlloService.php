<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Allo;
use App\Models\User;
use Illuminate\Support\Str;

class AlloService
{
    /**
     * Crée un nouvel allo avec la logique métier centralisée.
     *
     * - Génère un slug unique (à partir du slug fourni ou du titre).
     * - Renseigne created_by_id / updated_by_id.
     * - Assigne les admins via la relation many-to-many.
     *
     * @param  array<string, mixed>  $data  Données validées (StoreAlloRequest->validated()).
     * @param User|null  $actor  Utilisateur qui effectue l’action (peut être null).
     * @return Allo
     */
    public function createAllo(array $data, ?User $actor = null): Allo
    {
        /** @var array<int, int>|null $adminIds */
        $adminIds = null;

        if (array_key_exists('admin_ids', $data)) {
            /** @var array<int, mixed>|null $rawAdminIds */
            $rawAdminIds = $data['admin_ids'];

            if (is_array($rawAdminIds)) {
                $adminIds = array_map(
                    static fn (mixed $value): int => (int) $value,
                    $rawAdminIds
                );
            }

            unset($data['admin_ids']);
        }

        $data['slug'] = $this->generateUniqueSlugForCreate(
            title: (string) ($data['title'] ?? ''),
            providedSlug: isset($data['slug']) ? (string) $data['slug'] : null,
        );

        $actorId = $actor?->id;

        $data['created_by_id'] = $actorId;
        $data['updated_by_id'] = $actorId;

        /** @var Allo $allo */
        $allo = Allo::query()->create($data);

        if ($adminIds !== null) {
            $allo->admins()->sync($adminIds);
        }

        return $allo;
    }

    /**
     * Met à jour un allo existant.
     *
     * - Regénère le slug (ou utilise celui fourni) en restant unique.
     * - Met à jour updated_by_id.
     * - Met à jour l’assignation des admins (sync).
     *
     * @param Allo $allo
     * @param  array<string, mixed>  $data  Données validées (UpdateAlloRequest->validated()).
     * @param User|null  $actor
     * @return Allo
     */
    public function updateAllo(Allo $allo, array $data, ?User $actor = null): Allo
    {
        /** @var array<int, int>|null $adminIds */
        $adminIds = null;

        if (array_key_exists('admin_ids', $data)) {
            /** @var array<int, mixed>|null $rawAdminIds */
            $rawAdminIds = $data['admin_ids'];

            if (is_array($rawAdminIds)) {
                $adminIds = array_map(
                    static fn (mixed $value): int => (int) $value,
                    $rawAdminIds
                );
            }

            unset($data['admin_ids']);
        }

        $data['slug'] = $this->generateUniqueSlugForUpdate(
            allo: $allo,
            title: (string) ($data['title'] ?? ''),
            providedSlug: isset($data['slug']) ? (string) $data['slug'] : null,
        );

        $actorId = $actor?->id;
        $data['updated_by_id'] = $actorId;

        $allo->fill($data);
        $allo->save();

        if ($adminIds !== null) {
            $allo->admins()->sync($adminIds);
        }

        return $allo;
    }

    /**
     * Génère un slug unique pour la création d’un allo.
     *
     * @param  string  $title
     * @param  string|null  $providedSlug
     * @return string
     */
    private function generateUniqueSlugForCreate(string $title, ?string $providedSlug): string
    {
        $baseSlug = $this->normalizeSlug($title, $providedSlug);

        $slug = $baseSlug;
        $counter = 1;

        while (Allo::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Génère un slug unique pour la mise à jour d’un allo existant.
     *
     * On exclut l’allo courant de la vérification d’unicité.
     *
     * @param Allo $allo
     * @param  string  $title
     * @param  string|null  $providedSlug
     * @return string
     */
    private function generateUniqueSlugForUpdate(Allo $allo, string $title, ?string $providedSlug): string
    {
        $baseSlug = $this->normalizeSlug($title, $providedSlug);

        $slug = $baseSlug;
        $counter = 1;

        while (
        Allo::query()
            ->where('slug', $slug)
            ->where('id', '!=', $allo->id)
            ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Normalise un slug :
     * - si un slug est fourni → on le slugifie
     * - sinon → slug à partir du titre
     *
     * @param  string  $title
     * @param  string|null  $providedSlug
     * @return string
     */
    private function normalizeSlug(string $title, ?string $providedSlug): string
    {
        if ($providedSlug !== null && $providedSlug !== '') {
            return Str::slug($providedSlug);
        }

        return Str::slug($title);
    }
}
