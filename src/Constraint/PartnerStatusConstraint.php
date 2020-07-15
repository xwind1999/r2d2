<?php

declare(strict_types=1);

namespace App\Constraint;

class PartnerStatusConstraint extends AbstractChoiceConstraint
{
    public const PARTNER_STATUS_PROSPECT = 'prospect';
    public const PARTNER_STATUS_NEW_PARTNER = 'new partner';
    public const PARTNER_STATUS_PARTNER = 'partner';
    public const PARTNER_STATUS_INACTIVE_PARTNER = 'inactive partner';
    public const PARTNER_STATUS_FORMER_PARTNER = 'former partner';
    public const PARTNER_STATUS_BLACKLIST = 'blacklist';
    public const PARTNER_STATUS_WINBACK = 'winback';
    public const PARTNER_STATUS_CEASED = 'ceased';

    public const VALID_VALUES = [
        self::PARTNER_STATUS_PROSPECT,
        self::PARTNER_STATUS_NEW_PARTNER,
        self::PARTNER_STATUS_PARTNER,
        self::PARTNER_STATUS_INACTIVE_PARTNER,
        self::PARTNER_STATUS_FORMER_PARTNER,
        self::PARTNER_STATUS_BLACKLIST,
        self::PARTNER_STATUS_WINBACK,
        self::PARTNER_STATUS_CEASED,
    ];
}
