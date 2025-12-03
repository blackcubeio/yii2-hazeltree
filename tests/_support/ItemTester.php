<?php
/**
 * ItemTester.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests;

use Codeception\Actor;
use tests\_generated\ItemTesterActions;

/**
 * ItemTester actor for HazelTree tests
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ItemTester extends Actor
{
    use ItemTesterActions;
}
