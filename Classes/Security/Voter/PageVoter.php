<?php

namespace RozbehSharahi\Graphql3\Security\Voter;

use RozbehSharahi\Graphql3\Domain\Model\Page;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PageVoter implements VoterInterface
{
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        if (!$subject instanceof Page) {
            return self::ACCESS_ABSTAIN;
        }

        // Currently, pages are always allowed
        return self::ACCESS_GRANTED;
    }
}
