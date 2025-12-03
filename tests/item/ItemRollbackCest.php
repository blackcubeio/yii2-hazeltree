<?php
/**
 * ItemRollbackCest.php
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
use tests\TestBase;

/**
 * Test rollback scenarios in ItemTrait
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ItemRollbackCest extends TestBase
{
    /**
     * Generate a name longer than 255 characters to trigger validation failure
     */
    private function getInvalidName(): string
    {
        return str_repeat('x', 300);
    }

    /**
     * Test saveInto rollback on existing record with validation error
     */
    public function testSaveIntoRollbackOnExistingRecord(ItemTester $I): void
    {
        $item = Item::findOne(6); // Item 1.3
        $target = Item::findOne(3); // Item 1.2
        $originalPath = $item->path;
        $originalCount = Item::find()->count();

        // Set invalid name to trigger validation failure
        $item->name = $this->getInvalidName();

        $result = $item->saveInto($target);

        $I->assertFalse($result);
        $I->assertTrue($item->hasErrors());

        // Verify rollback: item should still have original path in DB
        $item->refresh();
        $I->assertEquals($originalPath, $item->path);
        $I->assertEquals($originalCount, Item::find()->count());
    }

    /**
     * Test saveInto rollback on new record with validation error
     */
    public function testSaveIntoRollbackOnNewRecord(ItemTester $I): void
    {
        $target = Item::findOne(3); // Item 1.2
        $originalCount = Item::find()->count();

        $item = new Item();
        $item->name = $this->getInvalidName();

        $result = $item->saveInto($target);

        $I->assertFalse($result);
        $I->assertTrue($item->hasErrors());

        // Verify rollback: item should not be in DB
        $I->assertEquals($originalCount, Item::find()->count());
    }

    /**
     * Test saveBefore rollback on existing record with validation error
     */
    public function testSaveBeforeRollbackOnExistingRecord(ItemTester $I): void
    {
        $item = Item::findOne(6); // Item 1.3
        $target = Item::findOne(3); // Item 1.2
        $originalPath = $item->path;
        $originalTargetPath = $target->path;
        $originalCount = Item::find()->count();

        $item->name = $this->getInvalidName();

        $result = $item->saveBefore($target);

        $I->assertFalse($result);
        $I->assertTrue($item->hasErrors());

        // Verify rollback
        $item->refresh();
        $target->refresh();
        $I->assertEquals($originalPath, $item->path);
        $I->assertEquals($originalTargetPath, $target->path);
        $I->assertEquals($originalCount, Item::find()->count());
    }

    /**
     * Test saveBefore rollback on new record with validation error
     */
    public function testSaveBeforeRollbackOnNewRecord(ItemTester $I): void
    {
        $target = Item::findOne(3); // Item 1.2
        $originalTargetPath = $target->path;
        $originalCount = Item::find()->count();

        // Store all original paths
        $originalPaths = [];
        foreach (Item::find()->all() as $existingItem) {
            $originalPaths[$existingItem->id] = $existingItem->path;
        }

        $item = new Item();
        $item->name = $this->getInvalidName();

        $result = $item->saveBefore($target);

        $I->assertFalse($result);
        $I->assertTrue($item->hasErrors());

        // Verify rollback: all items should have original paths
        $I->assertEquals($originalCount, Item::find()->count());
        foreach (Item::find()->all() as $existingItem) {
            $I->assertEquals($originalPaths[$existingItem->id], $existingItem->path);
        }
    }

    /**
     * Test saveAfter rollback on existing record with validation error
     */
    public function testSaveAfterRollbackOnExistingRecord(ItemTester $I): void
    {
        $item = Item::findOne(3); // Item 1.2
        $target = Item::findOne(6); // Item 1.3
        $originalPath = $item->path;
        $originalTargetPath = $target->path;
        $originalCount = Item::find()->count();

        $item->name = $this->getInvalidName();

        $result = $item->saveAfter($target);

        $I->assertFalse($result);
        $I->assertTrue($item->hasErrors());

        // Verify rollback
        $item->refresh();
        $target->refresh();
        $I->assertEquals($originalPath, $item->path);
        $I->assertEquals($originalTargetPath, $target->path);
        $I->assertEquals($originalCount, Item::find()->count());
    }

    /**
     * Test saveAfter rollback on new record with validation error (with next sibling)
     */
    public function testSaveAfterRollbackOnNewRecordWithNextSibling(ItemTester $I): void
    {
        $target = Item::findOne(2); // Item 1.1 (has sibling 1.2)
        $originalCount = Item::find()->count();

        // Store all original paths
        $originalPaths = [];
        foreach (Item::find()->all() as $existingItem) {
            $originalPaths[$existingItem->id] = $existingItem->path;
        }

        $item = new Item();
        $item->name = $this->getInvalidName();

        $result = $item->saveAfter($target);

        $I->assertFalse($result);
        $I->assertTrue($item->hasErrors());

        // Verify rollback: all items should have original paths
        $I->assertEquals($originalCount, Item::find()->count());
        foreach (Item::find()->all() as $existingItem) {
            $I->assertEquals($originalPaths[$existingItem->id], $existingItem->path);
        }
    }

    /**
     * Test saveAfter rollback on new record without next sibling
     */
    public function testSaveAfterRollbackOnNewRecordWithoutNextSibling(ItemTester $I): void
    {
        $target = Item::findOne(6); // Item 1.3 (last child of root)
        $originalCount = Item::find()->count();

        $item = new Item();
        $item->name = $this->getInvalidName();

        $result = $item->saveAfter($target);

        $I->assertFalse($result);
        $I->assertTrue($item->hasErrors());

        // Verify rollback
        $I->assertEquals($originalCount, Item::find()->count());
    }
}
