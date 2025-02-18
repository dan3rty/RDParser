<?php

class Token
{
    public const string T_RETURN = 'T_RETURN';
    public const string T_IF = 'T_IF';
    public const string T_ELSE = 'T_ELSE';
    public const string T_LET = 'T_LET';
    public const string T_CONST = 'T_CONST';
    public const string T_VAR = 'T_VAR';
    public const string T_AND = 'T_AND';
    public const string T_OR = 'T_OR';
    public const string T_FALSE = 'T_FALSE';
    public const string T_TRUE = 'T_TRUE';
    public const string T_MOD = 'T_MOD';
    public const string T_NOT = 'T_NOT';
    public const string T_EQ = 'T_EQ';
    public const string T_NEQ = 'T_NEQ';
    public const string T_LE = 'T_LE';
    public const string T_GE = 'T_GE';
    public const string T_LT = 'T_LT';
    public const string T_GT = 'T_GT';
    public const string T_PLUS = 'T_PLUS';
    public const string T_MINUS = 'T_MINUS';
    public const string T_MULT = 'T_MULT';
    public const string T_DIV = 'T_DIV';
    public const string T_ASSIGN = 'T_ASSIGN';
    public const string T_LPAREN = 'T_LPAREN';
    public const string T_RPAREN = 'T_RPAREN';
    public const string T_LBRACE = 'T_LBRACE';
    public const string T_RBRACE = 'T_RBRACE';
    public const string T_LBRACKET = 'T_LBRACKET';
    public const string T_RBRACKET = 'T_RBRACKET';
    public const string T_COMMA = 'T_COMMA';
    public const string T_EXPONENT = 'T_EXPONENT';
    public const string T_FLOAT = 'T_FLOAT';
    public const string T_INTEGER = 'T_INTEGER';
    public const string T_STRING = 'T_STRING';
    public const string T_IDENTIFIER = 'T_IDENTIFIER';
    public const string T_SEMICOLON = 'T_SEMICOLON';
    public const string T_WHITESPACE = 'T_WHITESPACE';
    public const string T_EOF = 'T_EOF';
    public const string T_ERROR = 'T_ERROR';

    public const int ERROR_UNCLOSED_STRING = 1;
    public const int ERROR_UNCLOSED_COMMENT = 2;
    public const int ERROR_UNKNOWN_SYMBOL = 3;
    public const int ERROR_INVALID_NUMBER_FORMAT = 4;

    public const array TOKEN_SPEC = [
        self::T_RETURN => '/\breturn\b/',
        self::T_IF => '/\bif\b/',
        self::T_ELSE => '/\belse\b/',
        self::T_LET => '/\blet\b/',
        self::T_CONST => '/\bconst\b/',
        self::T_VAR => '/\bvar\b/',
        self::T_AND => '/\bAND\b/',
        self::T_OR => '/\bOR\b/',
        self::T_FALSE => '/\bFALSE\b/',
        self::T_TRUE => '/\bTRUE\b/',
        self::T_MOD => '/\bMOD\b/',
        self::T_NOT => '/\bNOT\b/',
        self::T_EQ => '/==/',
        self::T_NEQ => '/!=/',
        self::T_LE => '/<=/',
        self::T_GE => '/>=/',
        self::T_LT => '/</',
        self::T_GT => '/>/',
        self::T_PLUS => '/\+/',
        self::T_MINUS => '/-/',
        self::T_MULT => '/\*/',
        self::T_DIV => '/\//',
        self::T_ASSIGN => '/=/',
        self::T_LPAREN => '/\(/',
        self::T_RPAREN => '/\)/',
        self::T_LBRACE => '/\{/',
        self::T_RBRACE => '/\}/',
        self::T_LBRACKET => '/\[/',
        self::T_RBRACKET => '/\]/',
        self::T_COMMA => '/,/',
        self::T_EXPONENT => '/\d+(?:\.\d*)?e[+-]?\d+|\.\d+e[+-]?\d+|\d*\.e[+-]?\d+/i',
        self::T_FLOAT => '/\d+\.\d*|\.\d+/',
        self::T_INTEGER => '/\d+/',
        self::T_STRING => '/"(?:\\\\.|[^"\\\\])*"/',
        self::T_IDENTIFIER => '/[a-zA-Z_]\w*(?:\.[a-zA-Z_]\w*)*/',
        self::T_SEMICOLON => '/;/',
        self::T_WHITESPACE => '/\s+/',
    ];

    public string $type;
    public string $value;
    public int $line;
    public int $column;
    public ?int $errorCode;

    public function __construct(string $type, string $value, int $line, int $column, ?int $errorCode = null)
    {
        $this->type = $type;
        $this->value = $value;
        $this->line = $line;
        $this->column = $column;
        $this->errorCode = $errorCode;
    }

    public function __toString(): string
    {
        if ($this->type === self::T_ERROR) {
            return "Token(type: $this->type, value: $this->value, error_code: $this->errorCode, line: $this->line, column: $this->column)";
        }
        return "Token(type: $this->type, value: $this->value, line: $this->line, column: $this->column)";
    }
}