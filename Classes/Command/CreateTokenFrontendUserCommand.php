<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Command;

use Doctrine\DBAL\Exception;
use RozbehSharahi\Graphql3\Domain\Model\JwtUser;
use RozbehSharahi\Graphql3\Security\JwtManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

class CreateTokenFrontendUserCommand extends Command
{
    public function __construct(protected JwtManager $jwtManager, protected ConnectionPool $connectionPool)
    {
        parent::__construct();
    }

    public function configure(): void
    {
        $this->addArgument('frontend-user-uid', InputArgument::REQUIRED, 'Frontend user uid');
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $frontendUser = $this
            ->connectionPool
            ->getQueryBuilderForTable('fe_users')
            ->select('*')
            ->from('fe_users')
            ->where('uid='.$input->getArgument('frontend-user-uid'))
            ->executeQuery()
            ->fetchAssociative()
        ;

        if (!$frontendUser) {
            $output->writeln('Frontend user with given uid was not found');

            return self::FAILURE;
        }

        $jwtManager = $this
            ->jwtManager
            ->withEnvironmentVariables()
        ;

        $user = JwtUser::createFromFrontendUserRow($frontendUser);

        $token = $jwtManager->create(new \DateTime('now + 3600 seconds'), $user->toPayload());

        $output->writeln($token);

        return Command::SUCCESS;
    }

    protected function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
