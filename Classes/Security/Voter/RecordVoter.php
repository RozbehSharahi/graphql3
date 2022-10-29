<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security\Voter;

use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class RecordVoter implements VoterInterface
{
    /**
     * @param array<int, string> $attributes
     */
    public function vote(TokenInterface $token, mixed $subject, array $attributes): int
    {
        if (!$subject instanceof Record) {
            return self::ACCESS_ABSTAIN;
        }

        if ($subject->isShowAtAnyLogin()) {
            return $token->getUser() ? self::ACCESS_GRANTED : self::ACCESS_DENIED;
        }

        if (empty($subject->getFrontendGroups())) {
            return self::ACCESS_GRANTED;
        }

        // At this point we should have a user or else deny access
        $user = $token->getUser();

        if (!$user instanceof JwtUser) {
            return self::ACCESS_DENIED;
        }

        foreach ($subject->getFrontendGroups() as $subjectUserGroupId) {
            if ($user->hasGroupId((int) $subjectUserGroupId)) {
                return self::ACCESS_GRANTED;
            }
        }

        return self::ACCESS_DENIED;
    }
}
