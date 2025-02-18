<?php
declare(strict_types=1);

require_once 'Token.php';

class Lexer
{
    /**
     * @throws RuntimeException
     */
    public function tokenize(string $input): array
    {
        $tokens = [];
        $offset = 0;
        $length = strlen($input);
        $line = 1;
        $column = 1;

        while ($offset < $length) {
            $offsetIncremented = false;

            if ($this->skipSingleLineComment($input, $offset, $line, $column)) {
                continue;
            }

            if ($this->handleMultiLineComment($input, $offset, $tokens, $line, $column)) {
                continue;
            }

            if ($this->handleString($input, $offset, $tokens, $line, $column)) {
                continue;
            }

            if ($this->handleUnknownSymbol($input, $offset, $tokens, $line, $column)) {
                continue;
            }

            $matched = false;

            foreach (Token::TOKEN_SPEC as $type => $pattern) {
                if ($this->attemptTokenMatch($input, $offset, $type, $pattern, $tokens, $line, $column)) {
                    $matched = true;
                    $offsetIncremented = true;
                    break;
                }
            }

            if (!$matched) {
                throw new RuntimeException("Неизвестный символ: '" . $input[$offset] . "' на строке $line, позиция $column");
            }

            if (!$offsetIncremented) {
                $offset++;
                $column++;
            }
        }

        $tokens[] = new Token(Token::T_EOF, '', $line, $column);
        return $tokens;
    }

    private function skipSingleLineComment(string $input, int &$offset, int &$line, int &$column): bool
    {
        if (preg_match('/\/\/.*/', substr($input, $offset), $matches, PREG_OFFSET_CAPTURE)) {
            if ($matches[0][1] === 0) {
                $commentLength = strlen($matches[0][0]);
                $offset += $commentLength;
                $column += $commentLength;
                return true;
            }
        }
        return false;
    }

    private function handleMultiLineComment(string $input, int &$offset, array &$tokens, int &$line, int &$column): bool
    {
        $length = strlen($input);

        if (substr($input, $offset, 2) === '/*') {
            $startOffset = $offset;
            $startLine = $line;
            $startColumn = $column;
            $offset += 2;
            $column += 2;
            $closed = false;

            while ($offset < $length - 1) {
                if (substr($input, $offset, 2) === '*/') {
                    $offset += 2;
                    $column += 2;
                    $closed = true;
                    break;
                }
                if ($input[$offset] === "\n") {
                    $line++;
                    $column = 1;
                } else {
                    $column++;
                }
                $offset++;
            }

            if (!$closed) {
                $tokens[] = new Token(Token::T_ERROR, substr($input, $startOffset), $startLine, $startColumn, Token::ERROR_UNCLOSED_COMMENT);
            }

            return true;
        }

        return false;
    }

    private function handleString(string $input, int &$offset, array &$tokens, int &$line, int &$column): bool
    {
        $length = strlen($input);

        if ($input[$offset] === '"') {
            $startOffset = $offset;
            $startLine = $line;
            $startColumn = $column;
            $offset++;
            $column++;

            while ($offset < $length) {
                if (($input[$offset] === '"') && $offset > 0 && $input[$offset - 1] !== '\\') {
                    $offset++;
                    $column++;
                    $tokens[] = new Token(Token::T_STRING, substr($input, $startOffset, $offset - $startOffset), $startLine, $startColumn);
                    return true;
                }
                if ($input[$offset] === "\n") {
                    $line++;
                    $column = 1;
                } else {
                    $column++;
                }
                $offset++;
            }

            $tokens[] = new Token(Token::T_ERROR, substr($input, $startOffset), $startLine, $startColumn, Token::ERROR_UNCLOSED_STRING);
            return true;
        }

        return false;
    }

    private function handleUnknownSymbol(string $input, int &$offset, array &$tokens, int &$line, int &$column): bool
    {
        if (preg_match('/[^\w\s.\/*\-+=<>!(){}\[\];,"]/', $input[$offset])) {
            $tokens[] = new Token(Token::T_ERROR, $input[$offset], $line, $column, Token::ERROR_UNKNOWN_SYMBOL);
            $offset++;
            $column++;
            return true;
        }
        return false;
    }

    private function attemptTokenMatch(string $input, int &$offset, string $type, string $pattern, array &$tokens, int &$line, int &$column): bool
    {
        $length = strlen($input);

        if (!preg_match($pattern, substr($input, $offset), $matches, PREG_OFFSET_CAPTURE)) {
            return false;
        }

        if ($matches[0][1] !== 0) {
            return false;
        }

        $value = $matches[0][0];
        $startLine = $line;
        $startColumn = $column;

        if (in_array($type, [Token::T_FLOAT, Token::T_INTEGER], true) && $offset + strlen($value) < $length) {
            $nextChar = $input[$offset + strlen($value)];
            if ($nextChar === '.') {
                if (preg_match('/^\d+/', substr($input, $offset + strlen($value) + 1), $numberMatches)) {
                    $invalidNumber = $value . '.' . $numberMatches[0];
                    $tokens[] = new Token(Token::T_ERROR, $invalidNumber, $startLine, $startColumn, Token::ERROR_INVALID_NUMBER_FORMAT);
                    $offset += strlen($invalidNumber);
                    $column += strlen($invalidNumber);
                    return true;
                }
            }
        }

        if ($type !== Token::T_WHITESPACE) {
            $tokens[] = new Token($type, $value, $startLine, $startColumn);
        }

        $offset += strlen($value);
        $column += strlen($value);
        return true;
    }
}