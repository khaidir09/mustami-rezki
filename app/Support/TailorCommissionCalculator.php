<?php

namespace App\Support;

/**
 * Pembagian komisi jahit (porsi 2/3 penjahit) untuk satu transaksi Internal.
 *
 * Penjahit tunggal: seluruh pool menjadi satu baris komisi.
 * Dua penjahit: pool dibagi berdasarkan bobot persentase, dibulatkan masing-masing
 * (jumlah dua bagian bisa meleset +/- 1 rupiah dari pool — trade-off yang diterima).
 */
class TailorCommissionCalculator
{
    /**
     * @return array{rows: array<int, array{user_id: int, amount: float}>, primary_pct: float, secondary_pct: ?float}
     */
    public static function split(
        float $tailorPool,
        int $primaryTailorId,
        ?int $secondaryTailorId,
        ?float $primaryPct,
        ?float $secondaryPct
    ): array {
        if ($secondaryTailorId) {
            $primaryPct = (float) $primaryPct;
            $secondaryPct = (float) $secondaryPct;

            $rows = [];
            $primaryAmount = round($tailorPool * $primaryPct / 100);
            $secondaryAmount = round($tailorPool * $secondaryPct / 100);

            if ($primaryAmount > 0) {
                $rows[] = ['user_id' => $primaryTailorId, 'amount' => $primaryAmount];
            }
            if ($secondaryAmount > 0) {
                $rows[] = ['user_id' => $secondaryTailorId, 'amount' => $secondaryAmount];
            }

            return ['rows' => $rows, 'primary_pct' => $primaryPct, 'secondary_pct' => $secondaryPct];
        }

        $rows = [];
        if ($tailorPool > 0) {
            $rows[] = ['user_id' => $primaryTailorId, 'amount' => $tailorPool];
        }

        return ['rows' => $rows, 'primary_pct' => 100.0, 'secondary_pct' => null];
    }

    /**
     * Total bobot kedua penjahit harus 100% (toleransi pembulatan kecil).
     */
    public static function percentagesSumTo100(?float $primaryPct, ?float $secondaryPct): bool
    {
        return abs(((float) $primaryPct + (float) $secondaryPct) - 100) <= 0.01;
    }
}
