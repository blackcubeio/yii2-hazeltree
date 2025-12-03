<?php
/**
 * ItemCest.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests\item;

use tests\_support\models\Item;
use tests\ItemTester;

/**
 * Test HazelTree basic operations
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ItemCest extends ItemBase
{
    public function testChildren(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 1]);
        $children = $item->getChildren();
        $I->assertCount(12, $item->children);
        foreach ($children->each() as $i => $child) {
            $I->assertEquals(($i + 2), $child->id);
        }
        $item = Item::findOne(['id' => 10]);
        $I->assertCount(2, $item->children);
        $I->assertEquals($this->itemList[11]['nodePath'], $item->children[0]->path);
        $I->assertEquals($this->itemList[12]['nodePath'], $item->children[1]->path);
    }

    public function testTree(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 1]);
        $tree = $item->getTree();
        $I->assertCount(13, $item->tree);
        foreach ($tree->each() as $i => $subItem) {
            $I->assertEquals(($i + 1), $subItem->id);
        }
        $item = Item::findOne(['id' => 10]);
        $I->assertCount(2, $item->children);
        $I->assertEquals($this->itemList[11]['nodePath'], $item->children[0]->path);
        $I->assertEquals($this->itemList[12]['nodePath'], $item->children[1]->path);
    }

    public function testSiblings(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 12]);
        $I->assertEquals(1, $item->getSiblings()->count());
        $I->assertEquals(11, $item->siblings[0]->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(3, $item->getSiblings()->count());
        $I->assertEquals(2, $item->siblings[0]->id);
        $I->assertEquals(3, $item->siblings[1]->id);
        $I->assertEquals(9, $item->siblings[2]->id);

        $item = Item::findOne(['id' => 1]);
        $I->assertEquals(0, $item->getSiblings()->count());
    }

    public function testSiblingsTrees(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 12]);
        $I->assertEquals(1, $item->getSiblingsTrees()->count());
        $I->assertEquals(11, $item->siblings[0]->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(9, $item->getSiblingsTrees()->count());
        $I->assertEquals(2, $item->siblingsTrees[0]->id);
        $I->assertEquals(3, $item->siblingsTrees[1]->id);
        $I->assertEquals(4, $item->siblingsTrees[2]->id);
        $I->assertEquals(5, $item->siblingsTrees[3]->id);
        $I->assertEquals(9, $item->siblingsTrees[4]->id);
        $I->assertEquals(10, $item->siblingsTrees[5]->id);
        $I->assertEquals(11, $item->siblingsTrees[6]->id);
        $I->assertEquals(12, $item->siblingsTrees[7]->id);
        $I->assertEquals(13, $item->siblingsTrees[8]->id);

        $item = Item::findOne(['id' => 13]);
        $I->assertEquals(3, $item->getSiblingsTrees()->count());
        $I->assertEquals(10, $item->siblingsTrees[0]->id);
        $I->assertEquals(11, $item->siblingsTrees[1]->id);
        $I->assertEquals(12, $item->siblingsTrees[2]->id);
    }

    public function testPreviousSiblingsTrees(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);
        $I->assertEquals(1, $item->getPreviousSiblingsTrees()->count());
        $I->assertEquals(2, $item->previousSiblingsTrees[0]->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(4, $item->getPreviousSiblingsTrees()->count());
        $I->assertEquals(2, $item->previousSiblingsTrees[0]->id);
        $I->assertEquals(3, $item->previousSiblingsTrees[1]->id);
        $I->assertEquals(4, $item->previousSiblingsTrees[2]->id);
        $I->assertEquals(5, $item->previousSiblingsTrees[3]->id);

        $item = Item::findOne(['id' => 2]);
        $I->assertEquals(0, $item->getPreviousSiblingsTrees()->count());

        $item = Item::findOne(['id' => 1]);
        $I->assertEquals(0, $item->getPreviousSiblingsTrees()->count());
    }

    public function testPreviousSiblings(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);
        $I->assertEquals(1, $item->getPreviousSiblings()->count());
        $I->assertEquals(2, $item->previousSiblings[0]->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(2, $item->getPreviousSiblings()->count());
        $I->assertEquals(2, $item->previousSiblings[0]->id);
        $I->assertEquals(3, $item->previousSiblings[1]->id);

        $item = Item::findOne(['id' => 2]);
        $I->assertEquals(0, $item->getPreviousSiblings()->count());
    }

    public function testPreviousSibling(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);
        $I->assertEquals(1, $item->getPreviousSibling()->count());
        $I->assertEquals(2, $item->previousSibling->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(3, $item->previousSibling->id);

        $item = Item::findOne(['id' => 2]);
        $I->assertEquals(0, $item->getPreviousSibling()->count());
    }

    public function testNextSiblingsTrees(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);
        $I->assertEquals(8, $item->getNextSiblingsTrees()->count());
        $I->assertEquals(6, $item->nextSiblingsTrees[0]->id);
        $I->assertEquals(7, $item->nextSiblingsTrees[1]->id);
        $I->assertEquals(8, $item->nextSiblingsTrees[2]->id);
        $I->assertEquals(9, $item->nextSiblingsTrees[3]->id);
        $I->assertEquals(10, $item->nextSiblingsTrees[4]->id);
        $I->assertEquals(11, $item->nextSiblingsTrees[5]->id);
        $I->assertEquals(12, $item->nextSiblingsTrees[6]->id);
        $I->assertEquals(13, $item->nextSiblingsTrees[7]->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(5, $item->getNextSiblingsTrees()->count());
        $I->assertEquals(9, $item->nextSiblingsTrees[0]->id);
        $I->assertEquals(10, $item->nextSiblingsTrees[1]->id);
        $I->assertEquals(11, $item->nextSiblingsTrees[2]->id);
        $I->assertEquals(12, $item->nextSiblingsTrees[3]->id);
        $I->assertEquals(13, $item->nextSiblingsTrees[4]->id);

        $item = Item::findOne(['id' => 13]);
        $I->assertEquals(0, $item->getNextSiblingsTrees()->count());

        $item = Item::findOne(['id' => 1]);
        $I->assertEquals(0, $item->getNextSiblingsTrees()->count());
    }

    public function testNextSiblings(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);
        $I->assertEquals(2, $item->getNextSiblings()->count());
        $I->assertEquals(6, $item->nextSiblings[0]->id);
        $I->assertEquals(9, $item->nextSiblings[1]->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(1, $item->getNextSiblings()->count());
        $I->assertEquals(9, $item->nextSiblings[0]->id);

        $item = Item::findOne(['id' => 9]);
        $I->assertEquals(0, $item->getNextSiblings()->count());
    }

    public function testNextSibling(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);
        $I->assertEquals(6, $item->nextSibling->id);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals(9, $item->nextSibling->id);

        $item = Item::findOne(['id' => 12]);
        $I->assertEquals(0, $item->getNextSibling()->count());
    }

    public function testSaveIntoChildren(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 6]);
        $pivot = Item::findOne(['id' => 3]);
        $item->saveInto($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['id' => 6]);
        $I->assertEquals('1.2.3', $item->path);
    }

    public function testSaveInto(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 11]);
        $pivot = Item::findOne(['id' => 12]);
        $item->saveInto($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['id' => 11]);
        $I->assertEquals('1.4.1.2.1', $item->path);
        $pivot = Item::findOne(['id' => 12]);
        $I->assertEquals('1.4.1.2', $pivot->path);
    }

    public function testSaveAfter(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 11]);
        $pivot = Item::findOne(['id' => 12]);
        $item->saveAfter($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['id' => 11]);
        $I->assertEquals('1.4.1.2', $item->path);
        $pivot = Item::findOne(['id' => 12]);
        $I->assertEquals('1.4.1.1', $pivot->path);
    }

    public function testSaveAfterTree(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 9]);
        $pivot = Item::findOne(['id' => 4]);
        $item->saveAfter($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['id' => 9]);
        $I->assertEquals('1.2.2', $item->path);
        $pivot = Item::findOne(['id' => 4]);
        $I->assertEquals('1.2.1', $pivot->path);
        $item = Item::findOne(['id' => 9]);
        $I->assertEquals('1.2.2', $item->path);
        $item = Item::findOne(['id' => 10]);
        $I->assertEquals('1.2.2.1', $item->path);
        $item = Item::findOne(['id' => 11]);
        $I->assertEquals('1.2.2.1.1', $item->path);
        $item = Item::findOne(['id' => 12]);
        $I->assertEquals('1.2.2.1.2', $item->path);
        $item = Item::findOne(['id' => 13]);
        $I->assertEquals('1.2.2.2', $item->path);
    }

    public function testSaveBefore(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 12]);
        $pivot = Item::findOne(['id' => 11]);
        $item->saveBefore($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['id' => 11]);
        $I->assertEquals('1.4.1.2', $item->path);
        $pivot = Item::findOne(['id' => 12]);
        $I->assertEquals('1.4.1.1', $pivot->path);
    }

    public function testSaveBeforeTree(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 9]);
        $pivot = Item::findOne(['id' => 5]);
        $item->saveBefore($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['id' => 9]);
        $I->assertEquals('1.2.2', $item->path);
        $pivot = Item::findOne(['id' => 4]);
        $I->assertEquals('1.2.1', $pivot->path);
        $item = Item::findOne(['id' => 9]);
        $I->assertEquals('1.2.2', $item->path);
        $item = Item::findOne(['id' => 10]);
        $I->assertEquals('1.2.2.1', $item->path);
        $item = Item::findOne(['id' => 11]);
        $I->assertEquals('1.2.2.1.1', $item->path);
        $item = Item::findOne(['id' => 12]);
        $I->assertEquals('1.2.2.1.2', $item->path);
        $item = Item::findOne(['id' => 13]);
        $I->assertEquals('1.2.2.2', $item->path);
        $item = Item::findOne(['id' => 5]);
        $I->assertEquals('1.2.3', $item->path);
    }

    public function testSaveNewIntoChildren(ItemTester $I): void
    {
        $item = new Item();
        $item->name = 'newitem';
        $pivot = Item::findOne(['id' => 3]);
        $item->saveInto($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['name' => 'newitem']);
        $I->assertEquals('1.2.3', $item->path);
    }

    public function testSaveNewInto(ItemTester $I): void
    {
        $item = new Item();
        $item->name = 'newitem';
        $pivot = Item::findOne(['id' => 12]);
        $item->saveInto($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['name' => 'newitem']);
        $I->assertEquals('1.4.1.2.1', $item->path);
        $pivot = Item::findOne(['id' => 12]);
        $I->assertEquals('1.4.1.2', $pivot->path);
    }

    public function testSaveNewAfter(ItemTester $I): void
    {
        $item = new Item();
        $item->name = 'newitem';
        $pivot = Item::findOne(['id' => 12]);
        $item->saveAfter($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['name' => 'newitem']);
        $I->assertEquals('1.4.1.3', $item->path);
        $pivot = Item::findOne(['id' => 12]);
        $I->assertEquals('1.4.1.2', $pivot->path);
    }

    public function testSaveNewAfterWithSibling(ItemTester $I): void
    {
        $item = new Item();
        $item->name = 'newitem';
        $pivot = Item::findOne(['id' => 11]);
        $item->saveAfter($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['name' => 'newitem']);
        $I->assertEquals('1.4.1.2', $item->path);
        $pivot = Item::findOne(['id' => 12]);
        $I->assertEquals('1.4.1.3', $pivot->path);
    }

    public function testSaveNewBefore(ItemTester $I): void
    {
        $item = new Item();
        $item->name = 'newitem';
        $pivot = Item::findOne(['id' => 11]);
        $item->saveBefore($pivot);
        $I->assertCount(0, $item->errors);
        $item = Item::findOne(['name' => 'newitem']);
        $I->assertEquals('1.4.1.1', $item->path);
        $pivot = Item::findOne(['id' => 11]);
        $I->assertEquals('1.4.1.2', $pivot->path);
        $pivot = Item::findOne(['id' => 12]);
        $I->assertEquals('1.4.1.3', $pivot->path);
    }
}
