<?php

/** @noinspection DuplicatedCode */

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use GraphQL\Type\Definition\Type;
use Psr\Http\Message\ServerRequestInterface;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Environment\Typo3Environment;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Session\CurrentSession;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Site\Entity\SiteLanguage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentRenderRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public const ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER = 'Error on creating typo3 frontend controller. Did your create a sys_template for your site?';

    public const ERROR_COULD_NOT_RESOLVE_SITE = 'No site available for content rendering.';

    public const ERROR_COULD_NOT_RESOLVE_FRONTEND_USER_ASPECT = 'No frontend user aspect was available on request object';

    public const ERROR_UNEXPECTED_DIRECT_RESPONSE = 'TSFE gave a direct response on determineId. Is there a page that conflicts with your graphql3 route?';

    public const ERROR_UNEXPECTED_TYPO3_CORE_CODE = 'Content rendering on graphql3 is very different on each typo3 version. However an unexpected state of core code was detected on runtime.';

    public function __construct(protected CurrentSession $currentSession, protected Typo3Environment $typo3Environment)
    {
    }

    public function supportsTable(TableConfiguration $table): bool
    {
        return 'tt_content' === $table->getName();
    }

    public function extendNodes(TableConfiguration $table, GraphqlNodeCollection $nodes): GraphqlNodeCollection
    {
        $node = GraphqlNode::create()
            ->withName('rendered')
            ->withType(Type::string())
            ->withResolver(function (Record $record) {
                $request = $this->currentSession->getRequest();
                $site = $request->getAttribute('site');
                $frontendUser = $request->getAttribute('frontend.user');
                $language = $record->getLanguage();
                $pageId = $record->getPid();

                if (!$site instanceof SiteInterface) {
                    throw new InternalErrorException(self::ERROR_COULD_NOT_RESOLVE_SITE);
                }

                if (!$frontendUser instanceof FrontendUserAuthentication) {
                    throw new InternalErrorException(self::ERROR_COULD_NOT_RESOLVE_FRONTEND_USER_ASPECT);
                }

                $tsfe = $this->createFrontendController($site, $language, $pageId, $frontendUser);

                if ($this->typo3Environment->isGreaterOrEqualVersion(12, 1)) {
                    return $this->renderContentVersion12Point1($tsfe, $request, $record);
                }

                if ($this->typo3Environment->isVersion(12, 0)) {
                    return $this->renderContentVersion12Point0($tsfe, $request, $record);
                }

                if ($this->typo3Environment->isVersion(11)) {
                    return $this->renderContentVersion11($tsfe, $request, $record);
                }

                throw new InternalErrorException('Unsupported typo3 version for graphql3.');
            })
        ;

        return $nodes->add($node);
    }

    protected function renderContentVersion12Point1(
        TypoScriptFrontendController $tsfe,
        ServerRequestInterface $request,
        Record $record,
    ): string {
        $directResponse = $tsfe->determineId($request);

        if ($directResponse) {
            throw new InternalErrorException(self::ERROR_UNEXPECTED_DIRECT_RESPONSE);
        }

        try {
            $request = $tsfe->getFromCache($request);
        } catch (\Throwable $e) {
            throw new InternalErrorException(self::ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER.': '.$e->getMessage());
        }

        $renderer = $this->createRenderer($tsfe);
        $renderer->setRequest($request);

        $renderedContent = $renderer->cObjGetSingle('RECORDS', [
            'tables' => 'tt_content',
            'source' => $record->getUid(),
            'dontCheckPid' => 1,
        ]);

        // now it gets wicked, we replace the uncached markers INT_script
        $tsfe->content = $renderedContent;
        $tsfe->cObj = $renderer;
        $tsfe->config['INTincScript'] ??= [];
        $tsfe->config['INTincScript_ext'] ??= [];
        $tsfe->config['INTincScript_ext']['divKey'] ??= null;

        // The run method will make sure to rollback tsfe and typo3_request globals
        $renderedContent = $this->run(function () use ($tsfe, $request) {
            $GLOBALS['TSFE'] = $tsfe;
            $GLOBALS['TYPO3_REQUEST'] = $request;
            $tsfe->INTincScript($request);

            return $tsfe->content;
        });

        $tsfe->releaseLocks();

        return $renderedContent;
    }

    protected function renderContentVersion12Point0(
        TypoScriptFrontendController $tsfe,
        ServerRequestInterface $request,
        Record $record,
    ): string {
        $directResponse = $tsfe->determineId($request);

        if ($directResponse) {
            throw new InternalErrorException(self::ERROR_UNEXPECTED_DIRECT_RESPONSE);
        }

        try {
            $tsfe->getFromCache($request);
        } catch (\Throwable $e) {
            throw new InternalErrorException(self::ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER.': '.$e->getMessage());
        }

        $renderer = $this->createRenderer($tsfe);
        $renderer->setRequest($request);

        $renderedContent = $renderer->cObjGetSingle('RECORDS', [
            'tables' => 'tt_content',
            'source' => $record->getUid(),
            'dontCheckPid' => 1,
        ]);

        // now it gets wicked, we replace the uncached markers INT_script
        $tsfe->content = $renderedContent;
        $tsfe->cObj = $renderer;
        $tsfe->config['INTincScript'] ??= [];
        $tsfe->config['INTincScript_ext'] ??= [];
        $tsfe->config['INTincScript_ext']['divKey'] ??= null;

        // The run method will make sure to rollback tsfe and typo3_request globals
        $renderedContent = $this->run(function () use ($tsfe, $request) {
            $GLOBALS['TSFE'] = $tsfe;
            $GLOBALS['TYPO3_REQUEST'] = $request;
            $tsfe->INTincScript($request);

            return $tsfe->content;
        });

        $tsfe->releaseLocks();

        RootlineUtility::{'purgeCaches'}();

        return $renderedContent;
    }

    protected function renderContentVersion11(
        TypoScriptFrontendController $tsfe,
        ServerRequest $request,
        Record $record,
    ): string {
        $directResponse = $tsfe->determineId($request);

        if ($directResponse) {
            throw new InternalErrorException(self::ERROR_UNEXPECTED_DIRECT_RESPONSE);
        }

        try {
            $tsfe->getFromCache($request);
        } catch (\Throwable $e) {
            throw new InternalErrorException(self::ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER.': '.$e->getMessage());
        }

        if (!method_exists($tsfe, 'getConfigArray')) {
            throw new InternalErrorException(self::ERROR_UNEXPECTED_TYPO3_CORE_CODE);
        }

        try {
            $tsfe->getConfigArray($request);
        } catch (\Throwable $e) {
            throw new InternalErrorException(self::ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER.': '.$e->getMessage());
        }

        $renderer = $this->createRenderer($tsfe);
        $renderer->setRequest($request);

        $renderedContent = $renderer->cObjGetSingle('RECORDS', [
            'tables' => 'tt_content',
            'source' => $record->getUid(),
            'dontCheckPid' => 1,
        ]);

        // now it gets wicked, we replace the uncached markers INT_script
        $tsfe->content = $renderedContent;
        $tsfe->cObj = $renderer;
        $tsfe->config['INTincScript'] ??= [];
        $tsfe->config['INTincScript_ext'] ??= [];
        $tsfe->config['INTincScript_ext']['divKey'] ??= null;

        // The run method will make sure to rollback tsfe and typo3_request globals
        $renderedContent = $this->run(function () use ($tsfe, $request) {
            $GLOBALS['TSFE'] = $tsfe;
            $GLOBALS['TYPO3_REQUEST'] = $request;
            $tsfe->INTincScript($request);

            return $tsfe->content;
        });

        $tsfe->releaseLocks();

        RootlineUtility::{'purgeCaches'}();

        return $renderedContent;
    }

    /**
     * Will make sure we roll back changes to tsfe and typo3_request globals.
     */
    protected function run(\Closure $job): mixed
    {
        $tsfeBackup = $GLOBALS['TSFE'] ?? null;
        $requestBackup = $GLOBALS['TYPO3_REQUEST'] ?? null;

        $result = $job();

        $GLOBALS['TSFE'] = $tsfeBackup;
        $GLOBALS['TYPO3_REQUEST'] = $requestBackup;

        return $result ?? null;
    }

    protected function createRenderer(TypoScriptFrontendController $tsfe): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class, $tsfe);
    }

    private function createFrontendController(
        SiteInterface $site,
        SiteLanguage $language,
        int $pageId,
        FrontendUserAuthentication $frontendUser,
    ): TypoScriptFrontendController {
        return GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $language,
            new PageArguments($pageId, '0', []),
            $frontendUser
        );
    }
}
