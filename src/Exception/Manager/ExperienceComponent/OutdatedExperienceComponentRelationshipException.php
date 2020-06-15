<?php

declare(strict_types=1);

namespace App\Exception\Manager\ExperienceComponent;

use App\Exception\ContextualException;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class OutdatedExperienceComponentRelationshipException extends ContextualException implements UnrecoverableExceptionInterface
{
    protected const CODE = 1500003;
    protected const MESSAGE = 'Outdated experience-component relationship received';
}
