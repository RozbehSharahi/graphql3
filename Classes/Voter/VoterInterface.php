<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Voter;

interface VoterInterface extends \Symfony\Component\Security\Core\Authorization\Voter\VoterInterface
{
    public const ATTRIBUTE_VIEW = 'VIEW';
    public const ATTRIBUTE_READ = 'VIEW';
    public const ATTRIBUTE_CREATE = 'VIEW';
    public const ATTRIBUTE_UPDATE = 'VIEW';
}
