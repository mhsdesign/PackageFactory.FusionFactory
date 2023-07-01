<?php

declare(strict_types=1);

namespace PackageFactory\FusionFactory\Domain;

use Neos\ContentRepository\Core\Projection\ContentGraph\Node;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Fusion\Core\RuntimeFactory;
use Neos\Neos\Domain\Model\Site;
use Neos\Neos\Domain\Model\SiteNodeName;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\FusionService;
use Neos\Neos\Domain\Service\SiteNodeUtility;
use PackageFactory\ComponentFactory\Domain\ComponentFactory;

function component(string $name, array $props): FusionObjectDto
{
    return new FusionObjectDto($name, $props);
}

function fusionRenderer(\Closure|ComponentFactory $factory): ComponentFactory
{
    if ($factory instanceof \Closure) {
        $factory = ComponentFactory::fromClosure($factory);
    }
    return $factory->wrap(function (FusionObjectDto $fusionObjectDto, Node $node, ControllerContext $controllerContext) use ($factory) {
        return render($fusionObjectDto, $node, $controllerContext);
    });
}

function render(FusionObjectDto $fusionObjectDto, Site|Node $siteOrNode, ControllerContext $controllerContext): mixed
{
    $objectManager = Bootstrap::$staticObjectManager;

    if (!$siteOrNode instanceof Site) {
        $siteNode = $objectManager->get(SiteNodeUtility::class)->findSiteNode($siteOrNode);
        if ($siteNode->nodeName === null) {
            throw new \Exception(sprintf('Site node "%s" is unnamed', $siteNode->nodeAggregateId->value), 1681286146);
        }
        $site = $objectManager->get(SiteRepository::class)->findOneByNodeName(SiteNodeName::fromNodeName($siteNode->nodeName))
            ?? throw new \Exception(sprintf('No site found for nodeNodeName "%s"', $siteNode->nodeName->value), 1677245517);
    } else {
        $site = $siteOrNode;
    }

    $runtime = $objectManager->get(RuntimeFactory::class)->createFromConfiguration(
        $objectManager->get(FusionService::class)->createFusionConfigurationFromSite($site),
        $controllerContext
    );

    $runtime->pushContext('fusionObjectDto', $fusionObjectDto);

    return $runtime->render('<PackageFactory.FusionFactory:FusionObjectDtoRenderer>');
}
