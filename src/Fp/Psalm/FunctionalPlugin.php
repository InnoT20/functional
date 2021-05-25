<?php

declare(strict_types=1);

namespace Fp\Psalm;

use Fp\Psalm\Hooks\FilterFunctionReturnTypeProvider;
use Fp\Psalm\Hooks\OptionGetOrElseMethodReturnTypeProvider;
use Fp\Psalm\Hooks\PartialFunctionReturnTypeProvider;
use Fp\Psalm\Hooks\PartitionFunctionReturnTypeProvider;
use Fp\Psalm\Hooks\PluckFunctionReturnTypeProvider;
use Fp\Psalm\Hooks\ProveTrueExpressionAnalyser;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

class FunctionalPlugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register = function(string $hook) use ($registration): void {
            class_exists($hook);
            $registration->registerHooksFromClass($hook);
        };

        $register(FilterFunctionReturnTypeProvider::class);
        $register(PartialFunctionReturnTypeProvider::class);
        $register(PartitionFunctionReturnTypeProvider::class);
        $register(PluckFunctionReturnTypeProvider::class);
        $register(OptionGetOrElseMethodReturnTypeProvider::class);
        $register(ProveTrueExpressionAnalyser::class);
    }
}
