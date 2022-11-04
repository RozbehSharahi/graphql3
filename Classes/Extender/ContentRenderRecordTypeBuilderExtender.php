<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Extender;

use GraphQL\Type\Definition\Type;
use RozbehSharahi\Graphql3\Builder\RecordTypeBuilderExtenderInterface;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNode;
use RozbehSharahi\Graphql3\Domain\Model\GraphqlNodeCollection;
use RozbehSharahi\Graphql3\Domain\Model\Record;
use RozbehSharahi\Graphql3\Domain\Model\Tca\TableConfiguration;
use RozbehSharahi\Graphql3\Exception\InternalErrorException;
use RozbehSharahi\Graphql3\Session\CurrentSession;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Http\ServerRequest;
use TYPO3\CMS\Core\Routing\PageArguments;
use TYPO3\CMS\Core\Site\Entity\SiteInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

class ContentRenderRecordTypeBuilderExtender implements RecordTypeBuilderExtenderInterface
{
    public const ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER = 'Error on creating typo3 frontend controller. Did your create a sys_template for your site?';

    public const ERROR_COULD_NOT_RESOLVE_SITE = 'No site available for content rendering.';

    public const ERROR_UNEXPECTED_DIRECT_RESPONSE = 'TSFE gave a direct response on determineId. Is there a page that conflicts with your graphql3 route?';

    public function __construct(protected CurrentSession $currentSession)
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
                return $this->run(function () use ($record) {
                    $request = $this->currentSession->getRequest();
                    $tsfe = $this->initFrontendController($request, $record);
                    $renderer = $this->createRendererByFrontendController($tsfe);

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
                    $tsfe->INTincScript($this->currentSession->getRequest());
                    $renderedContent = $tsfe->content;

                    $tsfe->releaseLocks();

                    RootlineUtility::purgeCaches();

                    return $renderedContent;
                });
            })
        ;

        return $nodes->add($node);
    }

    protected function initFrontendController(ServerRequest $request, Record $record): TypoScriptFrontendController
    {
        $site = $request->getAttribute('site');
        $language = $record->getLanguage();
        $pageArguments = new PageArguments($record->getPid(), '0', []);

        if (!$site instanceof SiteInterface) {
            throw new InternalErrorException(self::ERROR_COULD_NOT_RESOLVE_SITE);
        }

        $tsfe = GeneralUtility::makeInstance(
            TypoScriptFrontendController::class,
            GeneralUtility::makeInstance(Context::class),
            $site,
            $language,
            $pageArguments,
            $request->getAttribute('frontend.user')
        );

        $directResponse = $tsfe->determineId($request);
        if ($directResponse) {
            throw new InternalErrorException(self::ERROR_UNEXPECTED_DIRECT_RESPONSE);
        }

        // Should not be here !
        $GLOBALS['TSFE'] = $tsfe;
        $GLOBALS['TYPO3_REQUEST'] = $request;

        try {
            $tsfe->getFromCache($request);
        } catch (\Throwable $e) {
            throw new InternalErrorException(self::ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER.': '.$e->getMessage());
        }

        return $tsfe;
    }

    protected function run(\Closure $job): mixed
    {
        $tsfeBackup = $GLOBALS['TSFE'] ?? null;
        $requestBackup = $GLOBALS['TYPO3_REQUEST'] ?? null;

        $result = $job();

        $GLOBALS['TSFE'] = $tsfeBackup;
        $GLOBALS['TYPO3_REQUEST'] = $requestBackup;

        return $result ?? null;
    }

    protected function createRendererByFrontendController(TypoScriptFrontendController $tsfe): ContentObjectRenderer
    {
        return GeneralUtility::makeInstance(ContentObjectRenderer::class, $tsfe);
    }
}
