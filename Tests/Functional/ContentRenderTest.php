<?php

declare(strict_types=1);

namespace RozbehSharahi\Graphql3\Tests\Functional;

use GraphQL\Type\Schema;
use PHPUnit\Framework\TestCase;
use RozbehSharahi\Graphql3\Extender\ContentRenderRecordTypeBuilderExtender;
use RozbehSharahi\Graphql3\Registry\SchemaRegistry;
use RozbehSharahi\Graphql3\Tests\Functional\Core\FunctionalTrait;
use RozbehSharahi\Graphql3\Type\QueryType;

class ContentRenderTest extends TestCase
{
    use FunctionalTrait;

    public function testCanRenderContent(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->createRecord('sys_template', [
            'uid' => 1,
            'pid' => 1,
            'config' => '
                page = PAGE
                page.10 = TEXT
                page.10.value = page is configured

                tt_content = TEXT
                tt_content.field = bodytext
                tt_content.wrap = <div class="content-element">|</div>
            ',
        ]);

        $scope->createRecord('tt_content', [
            'uid' => 1,
            'pid' => 1,
            'header' => 'Header',
            'bodytext' => '<p>Content</p>',
        ]);

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                header
                bodytext
                rendered
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('<p>Content</p>', $response->get('data.content.bodytext'));
        self::assertSame(
            '<div class="content-element"><p>Content</p></div>',
            $response->get('data.content.rendered')
        );
    }

    public function testCanRenderContentAccordingToParentPageTypoScriptConfiguration(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope
            ->createRecord('pages', [
                'uid' => 2,
                'pid' => 1,
                'title' => 'Another page with special typo script configuration',
            ])
            ->createRecord('sys_template', [
                'uid' => 1,
                'pid' => 1,
                'config' => '
                    page = PAGE
                    page.10 = TEXT
                    page.10.value = page is configured
    
                    tt_content = TEXT
                    tt_content.field = bodytext
                ',
            ])
            ->createRecord('sys_template', [
                'uid' => 2,
                'pid' => 2,
                'config' => '
                    tt_content >
                    tt_content = TEXT
                    tt_content.value = Override
                ',
            ])
            ->createRecord('tt_content', [
                'uid' => 1,
                'pid' => 1,
                'header' => 'Header',
                'bodytext' => 'Content',
            ])
            ->createRecord('tt_content', [
                'uid' => 2,
                'pid' => 2,
                'header' => 'Header 2',
                'bodytext' => 'Content 2',
            ])
        ;

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $response = $scope->graphqlRequest('{
            contents {
                items {
                    header
                    bodytext
                    rendered
                }
            }
        }');

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Content', $response->get('data.contents.items.0.rendered'));
        self::assertSame('Override', $response->get('data.contents.items.1.rendered'));
    }

    public function testCanCommunicateMissingTypoScriptTemplate(): void
    {
        $scope = $this->getFunctionalScopeBuilder()->build();

        $scope->get(SchemaRegistry::class)->registerCreator(fn () => new Schema([
            'query' => $scope->get(QueryType::class),
        ]));

        $scope->createRecord('tt_content', ['uid' => 1, 'pid' => 1, 'header' => 'Some content']);

        $response = $scope->graphqlRequest('{
            content(uid: 1) {
                header
                rendered
            }
        }');

        self::assertSame(500, $response->getStatusCode());
        self::assertStringContainsString(
            ContentRenderRecordTypeBuilderExtender::ERROR_COULD_NOT_CREATE_FRONTEND_CONTROLLER,
            $response->get('errors.0.message')
        );
    }
}
