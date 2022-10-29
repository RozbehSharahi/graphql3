<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Command;

use DateTime;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Security\JwtManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateUserTokenCommand extends Command
{
    public function __construct(protected JwtManager $jwtManager)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $jwtManager = $this
            ->jwtManager
            ->withEnvironmentVariables()
        ;

        $helper = $this->getQuestionHelper();
        $username = $helper->ask($input, $output, new Question('Username: '));

        if (empty($username)) {
            throw new InternalErrorException('Username must not be empty!');
        }

        $roles = $helper->ask($input, $output, new Question('Roles (comma separated): '));

        if (empty($roles)) {
            throw new InternalErrorException('Roles must not be empty!');
        }

        $roles = explode(',', $roles);

        $token = $jwtManager->create(new DateTime('now + 3600 seconds'), [
            'username' => $username,
            'roles' => $roles,
        ]);

        $output->writeln($token);

        return Command::SUCCESS;
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
