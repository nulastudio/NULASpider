<?php

$indexed_array = [
    1,
    '',
    9 => 2,
    5 => null,
    4 => true,
    new \stdClass,
    ['',
        'test' => 'a',
    ],
];
$indexed_array2 = [
    1,
    '',
    '9' => 2,
    '5' => null,
    '4' => true,
    new \stdClass,
    ['',
        'test' => 'a',
    ],
];
$continuous_indexed_array = [
    1,
    '',
    2,
    null,
    true,
    new \stdClass,
    ['',
        'test' => 'a',
    ],
];
$assoc_array = [
    'a' => 'a',
    'b' => 2,
    'c' => [
        1,
        2,
        3,
    ],
];
$mixed_array = [
    '1',
    2    => '22',
    '3b' => '33',
];

echo json_encode($indexed_array), "\n";
echo json_encode($indexed_array2), "\n";
echo json_encode($continuous_indexed_array), "\n";
echo json_encode($assoc_array), "\n";
echo json_encode($mixed_array), "\n";

var_dump($indexed_array);
var_dump($indexed_array2);
var_dump($continuous_indexed_array);
var_dump($assoc_array);
var_dump($mixed_array);

echo "Testing isIndexedArray...\n";
if (!is_indexed_array($indexed_array)) {
    echo "indexed_array failed.\n";
}
if (!is_indexed_array($indexed_array2)) {
    echo "indexed_array2 failed.\n";
}
if (!is_indexed_array($continuous_indexed_array)) {
    echo "continuous_indexed_array failed.\n";
}
if (is_indexed_array($assoc_array)) {
    echo "assoc_array failed.\n";
}
if (is_indexed_array($mixed_array)) {
    echo "mixed_array failed.\n";
}
echo "Testing isContinuousIndexedArray...\n";
if (is_continuous_indexed_array($indexed_array)) {
    echo "indexed_array failed.\n";
}
if (is_continuous_indexed_array($indexed_array2)) {
    echo "indexed_array2 failed.\n";
}
if (!is_continuous_indexed_array($continuous_indexed_array)) {
    echo "continuous_indexed_array failed.\n";
}
if (is_continuous_indexed_array($assoc_array)) {
    echo "assoc_array failed.\n";
}
if (is_continuous_indexed_array($mixed_array)) {
    echo "mixed_array failed.\n";
}
echo "Testing isAssocArray...\n";
if (is_assoc_array($indexed_array)) {
    echo "indexed_array failed.\n";
}
if (is_assoc_array($indexed_array2)) {
    echo "indexed_array2 failed.\n";
}
if (is_assoc_array($continuous_indexed_array)) {
    echo "continuous_indexed_array failed.\n";
}
if (!is_assoc_array($assoc_array)) {
    echo "assoc_array failed.\n";
}
if (!is_assoc_array($mixed_array)) {
    echo "mixed_array failed.\n";
}
echo "Testing isMixedArray...\n";
if (is_mixed_array($indexed_array)) {
    echo "indexed_array failed.\n";
}
if (is_mixed_array($indexed_array2)) {
    echo "indexed_array2 failed.\n";
}
if (is_mixed_array($continuous_indexed_array)) {
    echo "continuous_indexed_array failed.\n";
}
if (is_mixed_array($assoc_array)) {
    echo "assoc_array failed.\n";
}
if (!is_mixed_array($mixed_array)) {
    echo "mixed_array failed.\n";
}
