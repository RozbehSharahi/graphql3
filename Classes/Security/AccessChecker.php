<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security;

use RozbehSharahi\Graphql3\Exception\UnauthorizedException;
use RozbehSharahi\Graphql3\Security\Voter\VoterInterface;
use RozbehSharahi\Graphql3\Session\CurrentSession;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Strategy\UnanimousStrategy;

class AccessChecker
{
    protected AccessDecisionManager $decisionManager;

    /**
     * @param iterable<VoterInterface> $voters
     */
    public function __construct(
        protected CurrentSession $currentSession,
        protected JwtManager $jwtManager,
        protected iterable $voters
    ) {
        $this->decisionManager = new AccessDecisionManager($voters, new UnanimousStrategy(true));
    }

    /**
     * @param array<int, string> $attributes
     */
    public function check(array $attributes, mixed $object = null): bool
    {
        $token = (new Token());

        $token->setUser($this->currentSession->hasUser() ? $this->currentSession->getUser() : null);

        return $this->decisionManager->decide($token, $attributes, $object, true);
    }

    /**
     * @param array<int, string> $attributes
     */
    public function assert(array $attributes, mixed $object = null): self
    {
        if (!$this->check($attributes, $object)) {
            throw new UnauthorizedException();
        }

        return $this;
    }
}
