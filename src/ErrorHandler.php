<?php

require_once 'Token.php';

class ErrorHandler
{
    public static function generateErrorMessage(Token $token): string
    {
        if ($token->type !== Token::T_ERROR) {
            return "Переданный токен не является ошибкой.";
        }

        $errorMessage = "Ошибка на строке {$token->line}, позиция {$token->column}: ";

        switch ($token->errorCode) {
            case Token::ERROR_UNCLOSED_STRING:
                $errorMessage .= "Незакрытая строка: '{$token->value}'.";
                break;
            case Token::ERROR_UNCLOSED_COMMENT:
                $errorMessage .= "Незакрытый комментарий: '{$token->value}'.";
                break;
            case Token::ERROR_UNKNOWN_SYMBOL:
                $errorMessage .= "Неизвестный символ: '{$token->value}'.";
                break;
            case Token::ERROR_INVALID_NUMBER_FORMAT:
                $errorMessage .= "Некорректный формат числа: '{$token->value}'.";
                break;
            default:
                $errorMessage .= "Неизвестная ошибка: '{$token->value}'.";
                break;
        }

        return $errorMessage;
    }
}