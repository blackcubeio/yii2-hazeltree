<?php
/**
 * ItemErrorCest.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests\item;

use blackcube\hazeltree\exceptions\InvalidNodeConfigurationException;
use tests\_support\models\Item;
use tests\ItemTester;

/**
 * Test HazelTree error handling
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ItemErrorCest extends ItemBase
{
    public function testExceptionSaveInto(ItemTester $I): void
    {
        $pivot = Item::findOne(['id' => 1]);
        $I->assertInstanceOf(Item::class, $pivot);

        $insertItem = new Item();
        $insertItem->setNodePath('1.3');
        $insertItem->name = 'Error item';

        $I->expectThrowable(InvalidNodeConfigurationException::class, function () use ($insertItem, $pivot) {
            $insertItem->saveInto($pivot);
        });
    }

    public function testExceptionSaveBefore(ItemTester $I): void
    {
        $pivot = Item::findOne(['id' => 12]);
        $I->assertInstanceOf(Item::class, $pivot);

        $insertItem = new Item();
        $insertItem->setNodePath('1.3');
        $insertItem->name = 'Error item';

        $I->expectThrowable(InvalidNodeConfigurationException::class, function () use ($insertItem, $pivot) {
            $insertItem->saveBefore($pivot);
        });
    }

    public function testExceptionSaveAfter(ItemTester $I): void
    {
        $pivot = Item::findOne(['id' => 12]);
        $I->assertInstanceOf(Item::class, $pivot);

        $insertItem = new Item();
        $insertItem->setNodePath('1.3');
        $insertItem->name = 'Error item';

        $I->expectThrowable(InvalidNodeConfigurationException::class, function () use ($insertItem, $pivot) {
            $insertItem->saveAfter($pivot);
        });
    }

    public function testCannotMoveIntoSelf(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);
        $I->assertInstanceOf(Item::class, $item);

        // Try to move item into itself
        $I->assertFalse($item->canMove($item->path));
    }

    public function testCannotMoveIntoChild(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);  // path: 1.2
        $child = Item::findOne(['id' => 4]); // path: 1.2.1

        $I->assertInstanceOf(Item::class, $item);
        $I->assertInstanceOf(Item::class, $child);

        // Try to move parent into child
        $I->assertFalse($item->canMove($child->path));
    }

    public function testCanMoveToSibling(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);  // path: 1.2
        $sibling = Item::findOne(['id' => 6]); // path: 1.3

        $I->assertInstanceOf(Item::class, $item);
        $I->assertInstanceOf(Item::class, $sibling);

        // Can move to sibling
        $I->assertTrue($item->canMove($sibling->path));
    }
}
