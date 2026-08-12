<?php

namespace App\Exceptions;

use Exception;

class InvalidTransactionException extends Exception
{
    public function __construct(string $message = 'Status transaksi tidak valid untuk tindakan ini.')
    {
        parent::__construct($message, 422);
    }
}
