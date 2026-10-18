<?php

use App\Models\VehicleMeasure;

test('relaciona o veículo apenas com as suas medições', function () {
    $vehicle = createVehicle();
    $otherVehicle = createVehicle();

    $measure = new VehicleMeasure;
    $measure->forceFill([
        'date_time' => '2026-10-09 10:00:00',
        'measure_type' => 'KILOMETER',
        'value' => 15000,
        'vehicle_id' => $vehicle->id,
    ])->save();

    expect($vehicle->measures)->toHaveCount(1)
        ->and($vehicle->measures->first())->toBeInstanceOf(VehicleMeasure::class)
        ->and($vehicle->measures->first()->id)->toBe($measure->id)
        ->and($otherVehicle->measures)->toHaveCount(0);
});
