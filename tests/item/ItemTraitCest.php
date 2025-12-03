<?php
/**
 * ItemTraitCest.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests\item;

use blackcube\hazeltree\helpers\MatrixHelper;
use blackcube\hazeltree\helpers\TreeHelper;
use tests\_support\models\Item;
use tests\ItemTester;

/**
 * Test ItemTrait methods coverage
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ItemTraitCest extends ItemBase
{
    public function testGetIsRoot(ItemTester $I): void
    {
        // Item 1 is root (level 1)
        $item = Item::findOne(['id' => 1]);
        $I->assertTrue($item->getIsRoot());
        $I->assertTrue($item->isRoot);

        // Item 2 is not root (level 2)
        $item = Item::findOne(['id' => 2]);
        $I->assertFalse($item->getIsRoot());
        $I->assertFalse($item->isRoot);

        // Item 11 is not root (level 4)
        $item = Item::findOne(['id' => 11]);
        $I->assertFalse($item->getIsRoot());
        $I->assertFalse($item->isRoot);
    }

    public function testGetParent(ItemTester $I): void
    {
        // Item 1 has no parent (root)
        $item = Item::findOne(['id' => 1]);
        $I->assertNull($item->parent);
        $I->assertEquals(0, $item->getParent()->count());

        // Item 2 parent is Item 1
        $item = Item::findOne(['id' => 2]);
        $I->assertNotNull($item->parent);
        $I->assertEquals(1, $item->parent->id);
        $I->assertEquals('1', $item->parent->path);

        // Item 4 parent is Item 3 (path 1.2.1 -> parent 1.2)
        $item = Item::findOne(['id' => 4]);
        $I->assertNotNull($item->parent);
        $I->assertEquals(3, $item->parent->id);
        $I->assertEquals('1.2', $item->parent->path);

        // Item 11 parent is Item 10 (path 1.4.1.1 -> parent 1.4.1)
        $item = Item::findOne(['id' => 11]);
        $I->assertNotNull($item->parent);
        $I->assertEquals(10, $item->parent->id);
        $I->assertEquals('1.4.1', $item->parent->path);
    }

    public function testGetParents(ItemTester $I): void
    {
        // Item 1 has no parents (root)
        $item = Item::findOne(['id' => 1]);
        $I->assertEquals(0, $item->getParents()->count());
        $I->assertEmpty($item->parents);

        // Item 2 has 1 parent (Item 1)
        $item = Item::findOne(['id' => 2]);
        $I->assertEquals(1, $item->getParents()->count());
        $I->assertEquals(1, $item->parents[0]->id);

        // Item 4 has 2 parents (Item 1, Item 3)
        $item = Item::findOne(['id' => 4]);
        $I->assertEquals(2, $item->getParents()->count());
        $I->assertEquals(1, $item->parents[0]->id);
        $I->assertEquals(3, $item->parents[1]->id);

        // Item 11 has 3 parents (Item 1, Item 9, Item 10)
        $item = Item::findOne(['id' => 11]);
        $I->assertEquals(3, $item->getParents()->count());
        $I->assertEquals(1, $item->parents[0]->id);
        $I->assertEquals(9, $item->parents[1]->id);
        $I->assertEquals(10, $item->parents[2]->id);
    }

    public function testSetNodePath(ItemTester $I): void
    {
        $item = new Item();
        $item->setNodePath('1.2.3');

        $I->assertEquals('1.2.3', $item->path);
        $I->assertEquals(3, $item->level);
        $I->assertNotNull($item->left);
        $I->assertNotNull($item->right);
        $I->assertLessThan($item->right, $item->left);

        // Test another path
        $item = new Item();
        $item->setNodePath('2.1.4.5');

        $I->assertEquals('2.1.4.5', $item->path);
        $I->assertEquals(4, $item->level);
    }

    public function testSetNodeMatrix(ItemTester $I): void
    {
        // Create matrix for path 1.2.3
        $matrix = TreeHelper::convertPathToMatrix('1.2.3');

        $item = new Item();
        $item->setNodeMatrix($matrix);

        $I->assertEquals('1.2.3', $item->path);
        $I->assertEquals(3, $item->level);
        $I->assertNotNull($item->left);
        $I->assertNotNull($item->right);

        // Test another path
        $matrix = TreeHelper::convertPathToMatrix('2.1.4');
        $item = new Item();
        $item->setNodeMatrix($matrix);

        $I->assertEquals('2.1.4', $item->path);
        $I->assertEquals(3, $item->level);
    }

    public function testGetNodeMatrix(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 1]);
        $matrix = $item->getNodeMatrix();
        $I->assertInstanceOf(MatrixHelper::class, $matrix);
        $I->assertEquals('1', TreeHelper::convertMatrixToPath($matrix));

        $item = Item::findOne(['id' => 4]);
        $matrix = $item->getNodeMatrix();
        $I->assertInstanceOf(MatrixHelper::class, $matrix);
        $I->assertEquals('1.2.1', TreeHelper::convertMatrixToPath($matrix));

        $item = Item::findOne(['id' => 11]);
        $matrix = $item->getNodeMatrix();
        $I->assertInstanceOf(MatrixHelper::class, $matrix);
        $I->assertEquals('1.4.1.1', TreeHelper::convertMatrixToPath($matrix));
    }

    public function testMoveInto(ItemTester $I): void
    {
        // Move Item 6 (1.3) into Item 3 (1.2)
        $item = Item::findOne(['id' => 6]);
        $target = Item::findOne(['id' => 3]);

        $I->assertEquals('1.3', $item->path);
        $I->assertEquals('1.2', $target->path);

        $item->moveInto($target);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals('1.2.3', $item->path);
        $I->assertEquals(3, $item->level);

        // Children should have moved too
        $child1 = Item::findOne(['id' => 7]);
        $I->assertEquals('1.2.3.1', $child1->path);

        $child2 = Item::findOne(['id' => 8]);
        $I->assertEquals('1.2.3.2', $child2->path);
    }

    public function testMoveBefore(ItemTester $I): void
    {
        // Move Item 6 (1.3) before Item 3 (1.2)
        $item = Item::findOne(['id' => 6]);
        $target = Item::findOne(['id' => 3]);

        $I->assertEquals('1.3', $item->path);
        $I->assertEquals('1.2', $target->path);

        $item->moveBefore($target);

        $item = Item::findOne(['id' => 6]);
        $I->assertEquals('1.2', $item->path);

        $target = Item::findOne(['id' => 3]);
        $I->assertEquals('1.3', $target->path);

        // Children of moved item should have moved too
        $child1 = Item::findOne(['id' => 7]);
        $I->assertEquals('1.2.1', $child1->path);

        $child2 = Item::findOne(['id' => 8]);
        $I->assertEquals('1.2.2', $child2->path);
    }

    public function testMoveAfter(ItemTester $I): void
    {
        // Move Item 3 (1.2) after Item 6 (1.3)
        $item = Item::findOne(['id' => 3]);
        $target = Item::findOne(['id' => 6]);

        $I->assertEquals('1.2', $item->path);
        $I->assertEquals('1.3', $target->path);

        $item->moveAfter($target);

        $item = Item::findOne(['id' => 3]);
        $I->assertEquals('1.3', $item->path);

        $target = Item::findOne(['id' => 6]);
        $I->assertEquals('1.2', $target->path);

        // Children of moved item should have moved too
        $child1 = Item::findOne(['id' => 4]);
        $I->assertEquals('1.3.1', $child1->path);

        $child2 = Item::findOne(['id' => 5]);
        $I->assertEquals('1.3.2', $child2->path);
    }

    public function testCanMove(ItemTester $I): void
    {
        $item = Item::findOne(['id' => 3]);

        // Can move to sibling
        $I->assertTrue($item->canMove('1.3'));

        // Cannot move into self
        $I->assertFalse($item->canMove('1.2'));

        // Cannot move into own child
        $I->assertFalse($item->canMove('1.2.1'));
        $I->assertFalse($item->canMove('1.2.2'));

        // Can move to completely different branch
        $I->assertTrue($item->canMove('1.4'));
        $I->assertTrue($item->canMove('1.4.1'));
    }

    public function testNodePathAndMatrixConsistency(ItemTester $I): void
    {
        // Test that setNodePath and setNodeMatrix produce consistent results
        $path = '1.2.3.4';

        $item1 = new Item();
        $item1->setNodePath($path);

        $matrix = TreeHelper::convertPathToMatrix($path);
        $item2 = new Item();
        $item2->setNodeMatrix($matrix);

        $I->assertEquals($item1->path, $item2->path);
        $I->assertEquals($item1->level, $item2->level);
        $I->assertEquals($item1->left, $item2->left);
        $I->assertEquals($item1->right, $item2->right);
    }

    public function testGetNodeMatrixConsistency(ItemTester $I): void
    {
        // Verify getNodeMatrix returns a matrix that converts back to the same path
        $paths = ['1', '1.1', '1.2', '1.2.1', '1.4.1.2'];
        foreach ($paths as $expectedPath) {
            $item = Item::findOne(['path' => $expectedPath]);
            $matrix = $item->getNodeMatrix();
            $actualPath = TreeHelper::convertMatrixToPath($matrix);
            $I->assertEquals($expectedPath, $actualPath, "Matrix conversion should be consistent for path $expectedPath");
        }
    }
}
