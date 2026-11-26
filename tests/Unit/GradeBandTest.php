<?php

use App\Enums\GradeBand;

it('maps scores to the correct letter band', function (float $score, string $letter) {
    expect(GradeBand::fromScore($score)->letter())->toBe($letter);
})->with([
    [2.0, 'F'],
    [2.5, 'F'],
    [3.0, 'D'],
    [3.5, 'D'],
    [4.0, 'C'],
    [4.5, 'C'],
    [5.0, 'B'],
    [5.5, 'B'],
    [6.0, 'A'],
]);

it('translates grade band labels in bulgarian and english', function () {
    app()->setLocale('bg');
    expect(GradeBand::Excellent->label())->toBe('Отличен');

    app()->setLocale('en');
    expect(GradeBand::Excellent->label())->toBe('Excellent');
    expect(GradeBand::Poor->letter())->toBe('F');
});
