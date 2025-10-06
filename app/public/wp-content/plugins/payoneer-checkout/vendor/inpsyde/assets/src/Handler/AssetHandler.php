<?php

/*
 * This file is part of the Assets package.
 *
 * (c) Inpsyde GmbH
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Syde\Vendor\Inpsyde\Assets\Handler;

use Syde\Vendor\Inpsyde\Assets\Asset;
interface AssetHandler
{
    /**
     * @param Asset $asset
     *
     * @return bool
     */
    public function register(Asset $asset): bool;
    /**
     * @param Asset $asset
     *
     * @return bool
     */
    public function enqueue(Asset $asset): bool;
}
