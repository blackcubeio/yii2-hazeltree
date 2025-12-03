<?php
/**
 * CompositeTestBase.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests;

use blackcube\hazeltree\helpers\TreeHelper;
use tests\_support\models\CompositeItem;
use Yii;

/**
 * Test base for HazelTree with composite primary key
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class CompositeTestBase
{
    /**
     * Test item list with path information
     * Structure (type_id=1):
     * 1
     * ├── 1.1
     * ├── 1.2
     * │   ├── 1.2.1
     * │   └── 1.2.2
     * └── 1.3
     *     ├── 1.3.1
     *     └── 1.3.2
     */
    protected array $itemList = [
        ['type_id' => 1, 'item_id' => 1, 'nodePath' => '1', 'name' => 'Item 1'],
        ['type_id' => 1, 'item_id' => 2, 'nodePath' => '1.1', 'name' => 'Item 1.1'],
        ['type_id' => 1, 'item_id' => 3, 'nodePath' => '1.2', 'name' => 'Item 1.2'],
        ['type_id' => 1, 'item_id' => 4, 'nodePath' => '1.2.1', 'name' => 'Item 1.2.1'],
        ['type_id' => 1, 'item_id' => 5, 'nodePath' => '1.2.2', 'name' => 'Item 1.2.2'],
        ['type_id' => 1, 'item_id' => 6, 'nodePath' => '1.3', 'name' => 'Item 1.3'],
        ['type_id' => 1, 'item_id' => 7, 'nodePath' => '1.3.1', 'name' => 'Item 1.3.1'],
        ['type_id' => 1, 'item_id' => 8, 'nodePath' => '1.3.2', 'name' => 'Item 1.3.2'],
    ];

    public function _before(): void
    {
        $this->createTable();
        $this->loadFixtures();
    }

    protected function createTable(): void
    {
        $db = Yii::$app->db;
        $tableName = CompositeItem::tableName();

        // Drop table if exists
        if ($db->schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        // Create table with composite primary key and InnoDB engine for transaction support
        $db->createCommand()->createTable($tableName, [
            'type_id' => 'INT NOT NULL',
            'item_id' => 'INT NOT NULL',
            'name' => 'VARCHAR(255)',
            'path' => 'VARCHAR(255) NOT NULL',
            'left' => 'DOUBLE NOT NULL',
            'right' => 'DOUBLE NOT NULL',
            'level' => 'INT NOT NULL',
            'PRIMARY KEY (type_id, item_id)',
        ], 'ENGINE=InnoDB')->execute();

        $db->createCommand()->createIndex('idx-composite_items-path', $tableName, 'path', true)->execute();
        $db->createCommand()->createIndex('idx-composite_items-left', $tableName, 'left')->execute();
        $db->createCommand()->createIndex('idx-composite_items-right', $tableName, 'right')->execute();
    }

    protected function loadFixtures(): void
    {
        foreach ($this->itemList as $config) {
            $item = new CompositeItem();
            $item->type_id = $config['type_id'];
            $item->item_id = $config['item_id'];
            $item->name = $config['name'];
            $item->setNodePath($config['nodePath']);
            $item->save(false);
        }
    }
}
