<?php
/**
 * CompositeItemCest.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests\item;

use tests\_support\models\CompositeItem;
use tests\CompositeTestBase;
use tests\ItemTester;

/**
 * Test HazelTree with composite primary key
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class CompositeItemCest extends CompositeTestBase
{
    public function testCompositePrimaryKey(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 1]);
        $I->assertNotNull($item);
        $I->assertEquals('1', $item->path);
        $I->assertEquals(['type_id' => 1, 'item_id' => 1], $item->getPrimaryKey(true));
    }

    public function testChildren(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 1]);
        $I->assertCount(7, $item->children);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertCount(2, $item->children);
        $I->assertEquals('1.2.1', $item->children[0]->path);
        $I->assertEquals('1.2.2', $item->children[1]->path);
    }

    public function testTree(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 1]);
        $I->assertCount(8, $item->tree);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertCount(3, $item->tree);
    }

    public function testParent(ItemTester $I): void
    {
        // Root has no parent
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 1]);
        $I->assertNull($item->parent);

        // Item 1.2 parent is Item 1
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertNotNull($item->parent);
        $I->assertEquals(1, $item->parent->item_id);

        // Item 1.2.1 parent is Item 1.2
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 4]);
        $I->assertNotNull($item->parent);
        $I->assertEquals(3, $item->parent->item_id);
    }

    public function testParents(ItemTester $I): void
    {
        // Root has no parents
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 1]);
        $I->assertEmpty($item->parents);

        // Item 1.2.1 has 2 parents
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 4]);
        $I->assertCount(2, $item->parents);
        $I->assertEquals(1, $item->parents[0]->item_id);
        $I->assertEquals(3, $item->parents[1]->item_id);
    }

    public function testSiblings(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertEquals(2, $item->getSiblings()->count());
        $I->assertEquals(2, $item->siblings[0]->item_id);
        $I->assertEquals(6, $item->siblings[1]->item_id);
    }

    public function testIsRoot(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 1]);
        $I->assertTrue($item->isRoot);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertFalse($item->isRoot);
    }

    public function testSaveInto(ItemTester $I): void
    {
        // Move Item 1.3 (item_id=6) into Item 1.2 (item_id=3)
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);

        $I->assertEquals('1.3', $item->path);
        $item->saveInto($target);
        $I->assertCount(0, $item->errors);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $I->assertEquals('1.2.3', $item->path);

        // Children should have moved too
        $child = CompositeItem::findOne(['type_id' => 1, 'item_id' => 7]);
        $I->assertEquals('1.2.3.1', $child->path);
    }

    public function testSaveAfter(ItemTester $I): void
    {
        // Move Item 1.2 (item_id=3) after Item 1.3 (item_id=6)
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);

        $item->saveAfter($target);
        $I->assertCount(0, $item->errors);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertEquals('1.3', $item->path);

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $I->assertEquals('1.2', $target->path);
    }

    public function testSaveBefore(ItemTester $I): void
    {
        // Move Item 1.3 (item_id=6) before Item 1.2 (item_id=3)
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);

        $item->saveBefore($target);
        $I->assertCount(0, $item->errors);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $I->assertEquals('1.2', $item->path);

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertEquals('1.3', $target->path);
    }

    public function testSaveNewInto(ItemTester $I): void
    {
        $item = new CompositeItem();
        $item->type_id = 1;
        $item->item_id = 100;
        $item->name = 'New Item';

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $item->saveInto($target);
        $I->assertCount(0, $item->errors);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 100]);
        $I->assertEquals('1.2.3', $item->path);
    }

    public function testSaveNewAfter(ItemTester $I): void
    {
        $item = new CompositeItem();
        $item->type_id = 1;
        $item->item_id = 101;
        $item->name = 'New Item';

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $item->saveAfter($target);
        $I->assertCount(0, $item->errors);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 101]);
        $I->assertEquals('1.4', $item->path);
    }

    public function testSaveNewBefore(ItemTester $I): void
    {
        $item = new CompositeItem();
        $item->type_id = 1;
        $item->item_id = 102;
        $item->name = 'New Item';

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $item->saveBefore($target);
        $I->assertCount(0, $item->errors);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 102]);
        $I->assertEquals('1.2', $item->path);

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertEquals('1.3', $target->path);
    }

    public function testCanMove(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);

        // Can move to sibling
        $I->assertTrue($item->canMove('1.3'));

        // Cannot move into self
        $I->assertFalse($item->canMove('1.2'));

        // Cannot move into own child
        $I->assertFalse($item->canMove('1.2.1'));
    }

    public function testMoveInto(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);

        $item->moveInto($target);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $I->assertEquals('1.2.3', $item->path);

        // Children should have moved too
        $child = CompositeItem::findOne(['type_id' => 1, 'item_id' => 7]);
        $I->assertEquals('1.2.3.1', $child->path);

        $child = CompositeItem::findOne(['type_id' => 1, 'item_id' => 8]);
        $I->assertEquals('1.2.3.2', $child->path);
    }

    public function testMoveBefore(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);

        $item->moveBefore($target);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $I->assertEquals('1.2', $item->path);

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertEquals('1.3', $target->path);
    }

    public function testMoveAfter(ItemTester $I): void
    {
        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);

        $item->moveAfter($target);

        $item = CompositeItem::findOne(['type_id' => 1, 'item_id' => 3]);
        $I->assertEquals('1.3', $item->path);

        $target = CompositeItem::findOne(['type_id' => 1, 'item_id' => 6]);
        $I->assertEquals('1.2', $target->path);
    }
}
