<?php
declare(strict_types=1);

require_once 'Lexer.php';
require_once 'ErrorHandler.php';

class Parser
{
    private array $tokens;
    private int $position = 0;
    private Token $currentToken;

    public function __construct(string $input)
    {
        $this->tokens = (new Lexer())->tokenize($input);
        $this->currentToken = $this->tokens[$this->position] ?? new Token(Token::T_EOF, '', 0, 0);
    }

    private function consume(string $expectedType): void
    {
        if ($this->currentToken->type === $expectedType) {
            $this->position++;
            $this->currentToken = $this->tokens[$this->position] ?? new Token(Token::T_EOF, '', 0, 0);
        } else {
            throw new RuntimeException("Ожидался токен типа $expectedType, но получен {$this->currentToken->type} на строке {$this->currentToken->line}, позиция {$this->currentToken->column}");
        }
    }

    private function match(string $type): bool
    {
        return $this->currentToken->type === $type;
    }

    public function parse(): void
    {
        foreach ($this->tokens as $token) {
            if ($token->type === Token::T_ERROR) {
                throw new RuntimeException(ErrorHandler::generateErrorMessage($token));
            }
        }

        $this->parseExpression();
        if (!$this->match(Token::T_EOF)) {
            throw new RuntimeException("Ожидался конец входных данных, но получен {$this->currentToken->type} на строке {$this->currentToken->line}, позиция {$this->currentToken->column}");
        }
    }

    private function parseExpression(): void
    {
        $this->parseLogicalOr();
    }

    private function parseLogicalOr(): void
    {
        $this->parseLogicalAnd();
        while ($this->match(Token::T_OR)) {
            $this->consume(Token::T_OR);
            $this->parseLogicalAnd();
        }
    }

    private function parseLogicalAnd(): void
    {
        $this->parseEquality();
        while ($this->match(Token::T_AND)) {
            $this->consume(Token::T_AND);
            $this->parseEquality();
        }
    }

    private function parseEquality(): void
    {
        $this->parseRelational();
        while ($this->match(Token::T_EQ) || $this->match(Token::T_NEQ)) {
            $this->consume($this->currentToken->type);
            $this->parseRelational();
        }
    }

    private function parseRelational(): void
    {
        $this->parseAdditive();
        while ($this->match(Token::T_LT) || $this->match(Token::T_GT) || $this->match(Token::T_LE) || $this->match(Token::T_GE)) {
            $this->consume($this->currentToken->type);
            $this->parseAdditive();
        }
    }

    private function parseAdditive(): void
    {
        $this->parseMultiplicative();
        while ($this->match(Token::T_PLUS) || $this->match(Token::T_MINUS)) {
            $this->consume($this->currentToken->type);
            $this->parseMultiplicative();
        }
    }

    private function parseMultiplicative(): void
    {
        $this->parseUnary();
        while ($this->match(Token::T_MULT) || $this->match(Token::T_DIV) || $this->match(Token::T_MOD)) {
            $this->consume($this->currentToken->type);
            $this->parseUnary();
        }
    }

    private function parseUnary(): void
    {
        if ($this->match(Token::T_MINUS) || $this->match(Token::T_NOT)) {
            $this->consume($this->currentToken->type);
            $this->parseUnary();
        } else {
            $this->parsePrimary();
        }
    }

    private function parsePrimary(): void
    {
        if ($this->match(Token::T_INTEGER) || $this->match(Token::T_FLOAT) || $this->match(Token::T_STRING) || $this->match(Token::T_EXPONENT)) {
            $this->consume($this->currentToken->type);
        } elseif ($this->match(Token::T_IDENTIFIER)) {
            $this->consume(Token::T_IDENTIFIER);
            if ($this->match(Token::T_LPAREN)) {
                $this->parseFunctionCall();
            } elseif ($this->match(Token::T_LBRACKET)) {
                $this->parseArrayAccess();
            }
        } elseif ($this->match(Token::T_LPAREN)) {
            $this->consume(Token::T_LPAREN);
            $this->parseExpression();
            $this->consume(Token::T_RPAREN);
        } elseif ($this->match(Token::T_TRUE) || $this->match(Token::T_FALSE)) {
            $this->consume($this->currentToken->type);
        } else {
            throw new RuntimeException("Ожидалось число, строка, идентификатор, TRUE, FALSE или выражение в скобках на строке {$this->currentToken->line}, позиция {$this->currentToken->column}");
        }
    }

    private function parseFunctionCall(): void
    {
        $this->consume(Token::T_LPAREN);
        if (!$this->match(Token::T_RPAREN)) {
            $this->parseExpression();
            while ($this->match(Token::T_COMMA)) {
                $this->consume(Token::T_COMMA);
                $this->parseExpression();
            }
        }
        $this->consume(Token::T_RPAREN);
    }

    private function parseArrayAccess(): void
    {
        $this->consume(Token::T_LBRACKET);
        $this->parseExpression();
        $this->consume(Token::T_RBRACKET);
        if ($this->match(Token::T_LBRACKET)) {
            $this->parseArrayAccess();
        }
    }
}