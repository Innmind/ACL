<?php
declare(strict_types = 1);

require 'vendor/autoload.php';

use Innmind\BlackBox\{
    Application,
    PHPUnit\Load,
    Runner\CodeCoverage,
};

Application::new($argv)
    ->map(static fn($app) => match (\getenv('BLACKBOX_ENV')) {
        'coverage' => $app
            ->scenariiPerProof(1)
            ->codeCoverage(
                CodeCoverage::of(
                    __DIR__.'/src/',
                    __DIR__.'/tests/',
                )
                    ->dumpTo('coverage.clover')
                    ->enableWhen(true),
            ),
        default => $app,
    })
    ->tryToProve(Load::directory(__DIR__.'/tests/'))
    ->exit();
