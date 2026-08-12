<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function __construct(string $message = 'Stok produk tidak mencukupi untuk transaksi ini.')
    {
        parent::__construct($message, 422);
    }
}
