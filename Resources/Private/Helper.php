<?php

declare(strict_types=1);

namespace PackageFactory\FusionFactory\Application;

use Neos\Fusion\Core\RuntimeFactory;
use Neos\Neos\Domain\Model\SiteNodeName;
use Neos\Neos\Domain\Repository\SiteRepository;
use Neos\Neos\Domain\Service\FusionService;
use PackageFactory\ComponentFactory\Application\RenderingStuff;
use PackageFactory\ComponentFactory\Domain\ComponentFactory;
use PackageFactory\ComponentFactory\Domain\CurrentControllerContext;
use PackageFactory\FusionFactory\Domain\FusionObjectDto;

function component(string $name, array $props): FusionObjectDto
{
    return new FusionObjectDto($name, $props);
}

function fusionRenderer(\Closure|ComponentFactory $factory): ComponentFactory
{
    if ($factory instanceof \Closure) {
        $factory = ComponentFactory::fromClosure($factory);
    }
    return $factory->wrap(function (FusionObjectDto $output, RenderingStuff $renderingStuff) {
        return render($output, $renderingStuff);
    });
}

function render(FusionObjectDto $fusionObjectDto, RenderingStuff $renderingStuff): mixed
{
    if ($renderingStuff->siteNode->nodeName === null) {
        throw new \Exception(sprintf('Site node "%s" is unnamed', $renderingStuff->siteNode->nodeAggregateId->value), 1681286146);
    }
    $site = $renderingStuff->di->get(SiteRepository::class)->findOneByNodeName(SiteNodeName::fromNodeName($renderingStuff->siteNode->nodeName))
        ?? throw new \Exception(sprintf('No site found for nodeNodeName "%s"', $renderingStuff->siteNode->nodeName->value), 1677245517);

    // todo cache runtime ...
    $runtime = $renderingStuff->di->get(RuntimeFactory::class)->createFromConfiguration(
        $renderingStuff->di->get(FusionService::class)->createFusionConfigurationFromSite($site),
        CurrentControllerContext::get()
    );

    $runtime->setEnableContentCache(false);

    $runtime->pushContext('fusionObjectDto', $fusionObjectDto);

    return $runtime->render('<PackageFactory.FusionFactory:FusionObjectDtoRenderer>');
}
