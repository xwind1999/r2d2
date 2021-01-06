<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\CMHStatusConstraint;
use App\Tests\ProphecyTestCase;

/**
 * @coversDefaultClass \App\Constraint\CMHStatusConstraint
 */
class CMHStatusConstraintTest extends ProphecyTestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(CMHStatusConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(CMHStatusConstraint::isValid('abcd'));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [['confirmed'], ['cancelled'], ['rejected']];
    }
}
