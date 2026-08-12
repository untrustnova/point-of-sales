<?php

use App\Services\QrCodeService;

test('qr code service dapat menghasilkan svg string', function () {
    $qrService = app(QrCodeService::class);

    $svg = $qrService->generateSvg('https://posnova.test');

    expect($svg)->toBeString()
        ->and($svg)->toContain('<svg');
});
