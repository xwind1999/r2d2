<?php

declare(strict_types=1);

namespace App\Tests\Constraint;

use App\Constraint\PartnerStatusConstraint;
use PHPUnit\Framework\TestCase;

class PartnerStatusConstraintTest extends TestCase
{
    /**
     * @dataProvider validValues
     */
    public function testIsValid(string $value): void
    {
        $this->assertTrue(PartnerStatusConstraint::isValid($value));
    }

    public function testIsInvalid(): void
    {
        $this->assertFalse(PartnerStatusConstraint::isValid('abcd'));
    }

    /**
     * @see testIsValid
     */
    public function validValues(): array
    {
        return [
            ['prospect'],
            ['new partner'],
            ['partner'],
            ['inactive partner'],
            ['former partner'],
            ['blacklist'],
            ['winback'],
            ['ceased'],
        ];
    }
}
