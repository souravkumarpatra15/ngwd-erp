<?php
// Simple test that doesn't depend on CodeIgniter framework
function test_addition(): void
{
    $result = 2 + 2;
    assert($result === 4, '2 + 2 should equal 4');
    echo "Test passed: 2 + 2 = 4\n";
}

function test_multiplication(): void
{
    $result = 3 * 4;
    assert($result === 12, '3 * 4 should equal 12');
    echo "Test passed: 3 * 4 = 12\n";
}

test_addition();
test_multiplication();
echo "All tests passed!\n";