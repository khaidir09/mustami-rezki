<?php

use App\Support\TailorCommissionCalculator;

// Pool = porsi 2/3 penjahit. Contoh: total_profit 90.000 -> pool 60.000.

it('memberi seluruh pool ke penjahit tunggal', function () {
    $result = TailorCommissionCalculator::split(60000, 1, null, null, null);

    expect($result['rows'])->toBe([
        ['user_id' => 1, 'amount' => 60000.0],
    ]);
    expect($result['primary_pct'])->toBe(100.0);
    expect($result['secondary_pct'])->toBeNull();
});

it('tidak membuat baris komisi bila pool nol atau negatif untuk penjahit tunggal', function () {
    expect(TailorCommissionCalculator::split(0, 1, null, null, null)['rows'])->toBe([]);
    expect(TailorCommissionCalculator::split(-5000, 1, null, null, null)['rows'])->toBe([]);
});

it('membagi rata 50/50 untuk dua penjahit', function () {
    $result = TailorCommissionCalculator::split(60000, 1, 2, 50, 50);

    expect($result['rows'])->toBe([
        ['user_id' => 1, 'amount' => 30000.0],
        ['user_id' => 2, 'amount' => 30000.0],
    ]);
    expect($result['primary_pct'])->toBe(50.0);
    expect($result['secondary_pct'])->toBe(50.0);
});

it('membagi sesuai bobot custom 70/30', function () {
    $result = TailorCommissionCalculator::split(60000, 1, 2, 70, 30);

    expect($result['rows'])->toBe([
        ['user_id' => 1, 'amount' => 42000.0],
        ['user_id' => 2, 'amount' => 18000.0],
    ]);
});

it('membulatkan tiap bagian secara terpisah (round masing-masing)', function () {
    // pool 10.000, 1/3 : 2/3 => 3333,33 dan 6666,67 -> dibulatkan
    $result = TailorCommissionCalculator::split(10000, 1, 2, 33.33, 66.67);

    expect($result['rows'])->toBe([
        ['user_id' => 1, 'amount' => 3333.0],
        ['user_id' => 2, 'amount' => 6667.0],
    ]);
});

it('mengabaikan penjahit yang bagiannya membulat ke nol', function () {
    $result = TailorCommissionCalculator::split(100, 1, 2, 100, 0);

    expect($result['rows'])->toBe([
        ['user_id' => 1, 'amount' => 100.0],
    ]);
});

it('memvalidasi total bobot harus 100 persen', function () {
    expect(TailorCommissionCalculator::percentagesSumTo100(50, 50))->toBeTrue();
    expect(TailorCommissionCalculator::percentagesSumTo100(70, 30))->toBeTrue();
    expect(TailorCommissionCalculator::percentagesSumTo100(60, 50))->toBeFalse();
    expect(TailorCommissionCalculator::percentagesSumTo100(40, 40))->toBeFalse();
});
