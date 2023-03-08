<?php

namespace PackageFactory\FusionFactory\Tests\Functional;

use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Fusion\View\FusionView;
use PackageFactory\FusionFactory\Domain\FusionObjectDto;

class FusionObjectDtoRendererTest extends FunctionalTestCase
{
    public function fusionObjectDtoProvider(): iterable
    {
        yield 'simple fusionObjectDto' => [
            'dto' => new FusionObjectDto(
                "Neos.Fusion:Value",
                [
                    "value" => 123
                ]
            ),
            'expected' => 123
        ];

        yield 'nested fusionObjectDto' => [
            'dto' => new FusionObjectDto(
                "Neos.Fusion:Value",
                [
                    "value" => new FusionObjectDto(
                        "Neos.Fusion:Value",
                        [
                            "value" => 12345
                        ]
                    ),
                ]
            ),
            'expected' => 12345
        ];

        yield 'auto created dataStructure for untyped children works' => [
            'dto' => new FusionObjectDto(
                "Neos.Fusion:DataStructure",
                [
                    "class" => [
                        "main" => "headline",
                        "color" => "blue",
                        "evenDeeper" => [
                            "foo" => "end"
                        ]
                    ]
                ]
            ),
            'expected' => [
                "class" => [
                    "main" => "headline",
                    "color" => "blue",
                    "evenDeeper" => [
                        "foo" => "end"
                    ]
                ]
            ]
        ];

        yield 'complex fusionObjectDto' => [
            'dto' => new FusionObjectDto(
                "Neos.Fusion:Join",
                [
                    "leadText" => new FusionObjectDto(
                        "Neos.Fusion:Value",
                        [
                            "value" => "lead"
                        ]
                    ),
                    "headline" => new FusionObjectDto(
                        "Neos.Fusion:Tag",
                        [
                            "tagName" => "h1",
                            "attributes" => [
                                "class" => [
                                    "main" => "headline",
                                    "color" => "blue"
                                ]
                            ],
                            "content" => "me to php"
                        ]
                    ),
                    "content" => "factories"
                ]
            ),
            'expected' => 'lead<h1 class="headline blue">me to php</h1>factories'
        ];
    }


    /**
     * @test
     * @dataProvider fusionObjectDtoProvider
     */
    public function fusionObjectDtoRenderer(FusionObjectDto $dto, mixed $expected)
    {
        $view = $this->createFusionView();
        $view->setFusionPath("<PackageFactory.FusionFactory:FusionObjectDtoRenderer>");
        $view->assign('fusionObjectDto', $dto);
        $result = $view->render();
        self::assertEquals($expected, $result);
    }

    private function createFusionView(): FusionView
    {
        $controllerContext = $this->getMockBuilder(ControllerContext::class)->disableOriginalConstructor()->getMock();
        $view = new FusionView();
        $view->setControllerContext($controllerContext);

        $view->setPackageKey('Any.Thing');

        $view->setFusionPathPatterns([
            "resource://Neos.Fusion/Private/Fusion/Root.fusion",
            "resource://PackageFactory.FusionFactory/Private/Fusion/Root.fusion"
        ]);
        return $view;
    }
}
