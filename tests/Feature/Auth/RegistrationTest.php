<?php

test('public registration screen is disabled in pos nova', function () {
    $response = $this->get('/register');

    $response->assertNotFound();
});
