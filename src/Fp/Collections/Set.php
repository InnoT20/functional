<?php

declare(strict_types=1);

namespace Fp\Collections;

/**
 * @psalm-immutable
 * @template-covariant TV
 * @extends Collection<TV>
 * @extends SetOps<TV>
 * @extends SetCasts<TV>
 */
interface Set extends Collection, SetOps, SetCasts
{

}
