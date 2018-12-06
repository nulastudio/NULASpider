<?php

function pattern(string $pattern)
{
    return __DIR__ . "/{$pattern}";
}

$patterns = [
    'ezSQL/PDO/pdo.php',
];

foreach ($patterns as $pattern) {
    require pattern($pattern);
}
