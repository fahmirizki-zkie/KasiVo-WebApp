<?php

namespace App\Exceptions;

use RuntimeException;

class AiQuotaExceededException extends RuntimeException
{
    protected $message = 'Kuota AI Anda sudah penuh atau token AI Anda sudah habis.';
}

