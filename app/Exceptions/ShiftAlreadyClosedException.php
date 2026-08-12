<?php

namespace App\Exceptions;

use Exception;

class ShiftAlreadyClosedException extends Exception
{
    public function __construct(string $message = 'Shift kasir tidak aktif atau sudah ditutup.')
    {
        parent::__construct($message, 422);
    }
}
