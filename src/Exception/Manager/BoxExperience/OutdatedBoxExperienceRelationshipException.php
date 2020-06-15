<?php

declare(strict_types=1);

namespace App\Exception\Manager\BoxExperience;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedBoxExperienceRelationshipException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500002;
    protected const MESSAGE = 'Outdated box-experience relationship received';
}
