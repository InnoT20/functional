<?php

declare(strict_types=1);

namespace Fp\Operations;

use Fp\Collections\HashMap;
use Fp\Collections\HashMapBuffer;
use Fp\Collections\HashTable;
use Fp\Collections\LinkedList;
use Fp\Collections\Map;
use Fp\Collections\Nil;
use Fp\Collections\Seq;
use Fp\Functional\Option\Option;
use Fp\Functional\State\StateFunctions;

/**
 * @template TK
 * @template TV
 * @psalm-immutable
 * @extends AbstractOperation<TK, TV>
 */
class GroupByOperation extends AbstractOperation
{
    /**
     * @template TKO
     * @psalm-param callable(TV, TK): TKO $f
     * @psalm-return HashMap<TKO, LinkedList<TV>>
     */
    public function __invoke(callable $f): Map
    {
        /**
         * @psalm-var HashTable<TKO, LinkedList<TV>> $state
         */
        $state = new HashTable();
        $stateBuilder = StateFunctions::infer(fn() => $state);
        $i = 0;

        foreach ($this->gen as $key => $value) {
            if ($i % 100 === 0) {
                $state = $stateBuilder->get()->run($state);
                $stateBuilder = StateFunctions::set($state);
            }

            $groupKey = $f($value, $key);

            $stateBuilder = $stateBuilder
                ->inspect(fn(HashTable $tbl) => HashMapBuffer::get($groupKey, $tbl))
                ->map(fn(Option $opt) => $opt->getOrElse(Nil::getInstance()))
                ->flatMap(fn(LinkedList $group) => HashMapBuffer::update($groupKey, $group->prepended($value)));

            $i++;
        }

        return $stateBuilder
            ->inspect(fn(HashTable $tbl) => new HashMap($tbl, empty($tbl->table)))
            ->run($state);
    }
}
