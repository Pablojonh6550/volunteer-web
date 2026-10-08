<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Consultas geográficas para models com colunas `latitude` e `longitude`.
 *
 * A distância é calculada no banco pela fórmula de Haversine (em km), o que
 * permite filtrar e ordenar por proximidade mantendo a paginação no SQL.
 * Antes do cálculo trigonométrico aplicamos um "bounding box" (faixa de
 * latitude/longitude) que aproveita o índice composto das tabelas.
 *
 * Funciona em MySQL 8 e em SQLite (usado nos testes), pois ambos oferecem
 * ASIN, SQRT, POWER, SIN, COS e RADIANS.
 */
trait HasCoordinates
{
    /** Raio médio da Terra em quilômetros. */
    public const EARTH_RADIUS_KM = 6371;

    /**
     * Adiciona a coluna calculada `distance_km` ao select.
     *
     * @param  Builder<static>  $query
     */
    public function scopeWithDistanceFrom(Builder $query, float $latitude, float $longitude): void
    {
        if ($query->getQuery()->columns === null) {
            $query->select($this->getTable().'.*');
        }

        $query->selectRaw($this->distanceSql().' AS distance_km', [$latitude, $latitude, $longitude]);
    }

    /**
     * Restringe aos registros dentro de `$radiusKm` quilômetros do ponto informado.
     *
     * @param  Builder<static>  $query
     */
    public function scopeNearby(Builder $query, float $latitude, float $longitude, float $radiusKm): void
    {
        $table = $this->getTable();

        // 1 grau de latitude ≈ 111,045 km; a longitude encolhe com o cosseno da latitude.
        $latDelta = $radiusKm / 111.045;
        $lngDelta = $radiusKm / max(111.045 * cos(deg2rad($latitude)), 0.000001);

        $query->whereNotNull("{$table}.latitude")
            ->whereNotNull("{$table}.longitude")
            ->whereBetween("{$table}.latitude", [$latitude - $latDelta, $latitude + $latDelta])
            ->whereBetween("{$table}.longitude", [$longitude - $lngDelta, $longitude + $lngDelta])
            ->whereRaw($this->distanceSql().' <= ?', [$latitude, $latitude, $longitude, $radiusKm]);
    }

    /**
     * Expressão SQL de Haversine. Bindings esperados: [lat, lat, lng].
     *
     * Os valores do usuário entram sempre como bindings (?); apenas o nome da
     * tabela (definido no model, nunca pelo usuário) é interpolado.
     *
     * @return literal-string
     */
    protected function distanceSql(): string
    {
        $table = $this->getTable();

        /** @var literal-string $sql */
        $sql = sprintf(
            '(%d * 2 * ASIN(SQRT(POWER(SIN(RADIANS(%2$s.latitude - ?) / 2), 2)'
            .' + COS(RADIANS(?)) * COS(RADIANS(%2$s.latitude))'
            .' * POWER(SIN(RADIANS(%2$s.longitude - ?) / 2), 2))))',
            self::EARTH_RADIUS_KM,
            $table,
        );

        return $sql;
    }
}
