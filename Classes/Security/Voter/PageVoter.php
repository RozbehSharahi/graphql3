<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security\Voter;

use RozbehSharahi\Graphql3\Domain\Model\Page;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PageVoter implements VoterInterface
{
    /**
     * @param array<int, string> $attributes
     */
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        if (!$subject instanceof Page) {
            return self::ACCESS_ABSTAIN;
        }

        if ($subject->isShowAtAnyLogin()) {
            return $token->getUser() ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
        }

        // Currently, pages are always allowed
        return self::ACCESS_GRANTED;
    }
}
