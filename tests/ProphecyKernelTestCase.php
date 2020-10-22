<?php

declare(strict_types=1);

namespace App\Tests;

use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProphecyKernelTestCase extends KernelTestCase
{
    use ProphecyTrait;
}
