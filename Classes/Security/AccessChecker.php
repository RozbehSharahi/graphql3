<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security;

use RozbehSharahi\Graphql3\Exception\UnauthorizedException;
use RozbehSharahi\Graphql3\Session\CurrentSession;
use RozbehSharahi\Graphql3\Voter\VoterInterface;
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

        if ($this->currentSession->hasToken()) {
            $this->currentSession->assertTokenIsValid();
        }

        if ($this->currentSession->hasUser()) {
            $token->setUser($this->currentSession->getUser());
        }

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
