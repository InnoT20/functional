<?php

declare(strict_types=1);

namespace Fp\Psalm;

use Fp\Psalm\Hook\AfterExpressionAnalysis\ProveTrueExpressionAnalyzer;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\CtorFunctionReturnTypeProvider;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\FilterFunctionReturnTypeProvider;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\PartialFunctionReturnTypeProvider;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\PartitionFunctionReturnTypeProvider;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\PartitionOfFunctionReturnTypeProvider;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\PluckFunctionReturnTypeProvider;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\SequenceEitherFunctionReturnTypeProvider;
use Fp\Psalm\Hook\FunctionReturnTypeProvider\SequenceOptionFunctionReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\CollectionFilterMethodReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\EitherGetReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\FoldMethodReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\MapTapNMethodReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\OptionFilterMethodReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\OptionGetReturnTypeProvider;
use Fp\Psalm\Hook\MethodReturnTypeProvider\PluckMethodReturnTypeProvider;
use Psalm\Plugin\PluginEntryPointInterface;
use Psalm\Plugin\RegistrationInterface;
use SimpleXMLElement;

/**
 * Plugin entrypoint
 */
class FunctionalPlugin implements PluginEntryPointInterface
{
    public function __invoke(RegistrationInterface $registration, ?SimpleXMLElement $config = null): void
    {
        $register = function(string $hook) use ($registration): void {
            if (class_exists($hook)) {
                $registration->registerHooksFromClass($hook);
            }
        };

        $register(PartialFunctionReturnTypeProvider::class);
        $register(PartitionFunctionReturnTypeProvider::class);
        $register(PartitionOfFunctionReturnTypeProvider::class);
        $register(PluckFunctionReturnTypeProvider::class);

        $register(FilterFunctionReturnTypeProvider::class);
        $register(CollectionFilterMethodReturnTypeProvider::class);
        $register(OptionFilterMethodReturnTypeProvider::class);

        $register(ProveTrueExpressionAnalyzer::class);
        $register(EitherGetReturnTypeProvider::class);
        $register(OptionGetReturnTypeProvider::class);

        $register(SequenceOptionFunctionReturnTypeProvider::class);
        $register(SequenceEitherFunctionReturnTypeProvider::class);

        $register(FoldMethodReturnTypeProvider::class);
        $register(MapTapNMethodReturnTypeProvider::class);
        $register(PluckMethodReturnTypeProvider::class);
        $register(CtorFunctionReturnTypeProvider::class);
    }
}
