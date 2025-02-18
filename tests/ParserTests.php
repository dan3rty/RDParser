<?php
declare(strict_types=1);

require_once "../src/Parser.php";
require_once "../src/Lexer.php";

use PHPUnit\Framework\TestCase;

class ParserTests extends TestCase
{
    public function testLexerTokenizesSimpleExpression(): void
    {
        $lexer = new Lexer();
        $tokens = $lexer->tokenize("1 + 2");

        $this->assertCount(4, $tokens);
        $this->assertEquals(Token::T_INTEGER, $tokens[0]->type);
        $this->assertEquals(Token::T_PLUS, $tokens[1]->type);
        $this->assertEquals(Token::T_INTEGER, $tokens[2]->type);
        $this->assertEquals(Token::T_EOF, $tokens[3]->type);
    }

    public function testLexerIgnoresWhitespace(): void
    {
        $lexer = new Lexer();
        $tokens = $lexer->tokenize("  1  +   2  ");

        $this->assertCount(4, $tokens);
        $this->assertEquals(Token::T_INTEGER, $tokens[0]->type);
        $this->assertEquals(Token::T_PLUS, $tokens[1]->type);
        $this->assertEquals(Token::T_INTEGER, $tokens[2]->type);
        $this->assertEquals(Token::T_EOF, $tokens[3]->type);
    }

    public function testLexerHandlesComments(): void
    {
        $lexer = new Lexer();
        $tokens = $lexer->tokenize("1 + 2 // comment");

        $this->assertCount(4, $tokens);
        $this->assertEquals(Token::T_INTEGER, $tokens[0]->type);
        $this->assertEquals(Token::T_PLUS, $tokens[1]->type);
        $this->assertEquals(Token::T_INTEGER, $tokens[2]->type);
        $this->assertEquals(Token::T_EOF, $tokens[3]->type);
    }

    public function testParserParsesValidExpression(): void
    {
        $parser = new Parser("1 + 2 * 3");
        $parser->parse();
        $this->assertTrue(true);
    }

    public function testParserDetectsSyntaxError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Ожидалось число, строка, идентификатор, TRUE, FALSE или выражение в скобках на строке 1, позиция 5");

        $parser = new Parser("1 + * 2");
        $parser->parse();
    }

    public function testParserDetectsUnexpectedEnd(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage("Ожидалось число, строка, идентификатор, TRUE, FALSE или выражение в скобках на строке 1, позиция 5");

        $parser = new Parser("1 + ");
        $parser->parse();
    }

    public function testValidExpressions()
    {
        $expressions = [
            'a + b * c',
            '(a + b) * c',
            'a[5] + b(3, d[2])',
            'x AND y OR z',
            'NOT a[7][a+5][b(3.5, c.d[f * ab])] OR 15 * (r - br MOD 5) AND TRUE',
            'func(x, y + 3, arr[2])',
            'a < b AND c > d OR e == f',
            '12.5 + 3.14 * (b - c)',
            'TRUE OR FALSE AND NOT x',
            'arr[0][1][2] + func(1, 2, 3)'
        ];

        $this->expectNotToPerformAssertions();
        foreach ($expressions as $expr) {
            $parser = new Parser($expr);
            $parser->parse();
        }
    }

    public function testInvalidExpressions()
    {
        $invalidExpressions = [
            'a + ',
            '(a + b',
            'a * * b',
            'func(,)',
            'arr[5',
            'a + b c',
            '1 + (2 * 3',
            'a OR AND b',
            'NOT OR a',
            '[a + b]',
        ];

        $this->expectException(RuntimeException::class);
        foreach ($invalidExpressions as $expr) {
            $parser = new Parser($expr);
            $parser->parse();
        }
    }
}
