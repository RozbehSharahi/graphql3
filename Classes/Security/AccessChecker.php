<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Security;

use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Exception\BadRequestException;
use RozbehSharahi\Graphql3\Exception\UnauthorizedException;
use RozbehSharahi\Graphql3\Security\Voter\VoterInterface;
use RozbehSharahi\Graphql3\Session\CurrentRequest;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManager;
use Symfony\Component\Security\Core\Authorization\Strategy\UnanimousStrategy;

class AccessChecker
{
    protected AccessDecisionManager $decisionManager;

    /**
     * @param iterable<VoterInterface> $voters
     */
    public function __construct(
        protected CurrentRequest $currentRequest,
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
        $token = new Token();

        $jwtManager = $this->jwtManager->withEnvironmentVariables();

        if ($this->currentRequest->hasToken() && $jwtManager->isExpired($this->currentRequest->getToken())) {
            throw new BadRequestException('Expired jwt token provided.');
        }

        if ($this->currentRequest->hasToken() && !$jwtManager->isValid($this->currentRequest->getToken())) {
            throw new BadRequestException('Invalid jwt token.');
        }

        if ($this->currentRequest->hasToken()) {
            $token->setUser(JwtUser::createFromPayload($jwtManager->read($this->currentRequest->getToken())));
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
