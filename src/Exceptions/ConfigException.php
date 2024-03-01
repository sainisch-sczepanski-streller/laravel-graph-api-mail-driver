<?php

namespace LaravelGraphApiMailDriver\Exceptions;

use Exception;
use Throwable;

class ConfigException extends Exception{

    public function __construct(array $messages, $code = 0, Throwable $previous = null){

        $message = $this->buildMessage($messages);

        parent::__construct($message, $code, $previous);
    }

    private function buildMessage(array $messages): string{

        $result = 'Check your config/mail.php: ';

        foreach($messages as $message){

            $result .= implode(PHP_EOL, $message) . PHP_EOL;
        }

        return $result;
    }
}
