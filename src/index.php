<?php
declare(strict_types=1);

require_once './Parser.php';

$input = 'NOT a[7][a+5][b(3.5, c.d[f * ab])] OR 15 * (r - br MOD 5) AND TRUE';

$parser = new Parser($input);

try {
    $parser->parse();
    echo "Код корректен!\n";
} catch (RuntimeException $e) {
    echo "Ошибка: " . $e->getMessage() . "\n";
}