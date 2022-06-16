<?php

declare(strict_types=1);

namespace Fp\Collections;

/**
 * @template-covariant TV
 * @extends SetChainableOps<TV>
 * @extends SetTerminalOps<TV>
 * @extends SetCastableOps<TV>
 */
interface SetOps extends SetChainableOps, SetTerminalOps, SetCastableOps
{

}
