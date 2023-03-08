<?php
declare(strict_types=1);

namespace PackageFactory\FusionFactory\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class FusionObjectDto
{
    public function __construct(
        private string $name,
        private array $arguments
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }
}
