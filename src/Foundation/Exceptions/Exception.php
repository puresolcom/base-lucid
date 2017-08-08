<?php

namespace Awok\Foundation\Exceptions;

use Illuminate\Validation\Validator;

class Exception extends \Exception
{
    public function __construct(
        $message,
        $code = 0,
        \Exception $previous = null
    ) {

        if ($message instanceof Validator) {
            $message = $message->messages()->all();
        }

        if (is_array($message)) {
            $message = implode("\n", $message);
        }

        parent::__construct($message, $code, $previous);
    }
}