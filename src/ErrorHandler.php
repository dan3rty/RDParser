<?php

require_once 'Token.php';

class ErrorHandler
{
    public static function generateErrorMessage(Token $token): string
    {
        if ($token->type !== Token::T_ERROR) {
            return "Переданный токен не является ошибкой.";
        }

        $errorMessage = "Ошибка на строке $token->line, позиция $token->column: ";

        $errorMessage .= match ($token->errorCode) {
            Token::ERROR_UNCLOSED_STRING => "Незакрытая строка: '$token->value'.",
            Token::ERROR_UNCLOSED_COMMENT => "Незакрытый комментарий: '$token->value'.",
            Token::ERROR_UNKNOWN_SYMBOL => "Неизвестный символ: '$token->value'.",
            Token::ERROR_INVALID_NUMBER_FORMAT => "Некорректный формат числа: '$token->value'.",
            default => "Неизвестная ошибка: '$token->value'.",
        };

        return $errorMessage;
    }
}