<?php

namespace RozbehSharahi\Graphql3\Security;

use RozbehSharahi\Graphql3\Exception\GraphqlException;
use RozbehSharahi\Graphql3\Security\Voter\VoterInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Strategy\UnanimousStrategy;

class AccessChecker
{
    protected AccessDecisionManager $decisionManager;

    /**
     * @param iterable<VoterInterface> $voters
     */
    public function __construct(protected iterable $voters)
    {
        $this->decisionManager = new AccessDecisionManager($voters, new UnanimousStrategy());
    }

    public function check(array $attributes, mixed $object = null): bool
    {
        return $this->decisionManager->decide(new SessionToken(), $attributes, $object, true);
    }

    public function assert(array $attributes, mixed $object = null): self
    {
        if (!$this->check($attributes, $object)) {
            throw GraphqlException::createClientSafe('Access denied.');
        }

        return $this;
    }
}
