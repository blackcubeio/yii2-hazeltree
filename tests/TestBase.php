<?php
/**
 * TestBase.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests;

use blackcube\hazeltree\helpers\TreeHelper;
use tests\_support\models\Item;
use Yii;

/**
 * Test base for HazelTree
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class TestBase
{
    /**
     * Test item list with path information
     * Structure:
     * 1
     * ├── 1.1
     * ├── 1.2
     * │   ├── 1.2.1
     * │   └── 1.2.2
     * ├── 1.3
     * │   ├── 1.3.1
     * │   └── 1.3.2
     * └── 1.4
     *     ├── 1.4.1
     *     │   ├── 1.4.1.1
     *     │   └── 1.4.1.2
     *     └── 1.4.2
     */
    protected array $itemList = [
        1 => ['nodePath' => '1', 'name' => 'Item 1'],
        2 => ['nodePath' => '1.1', 'name' => 'Item 1.1'],
        3 => ['nodePath' => '1.2', 'name' => 'Item 1.2'],
        4 => ['nodePath' => '1.2.1', 'name' => 'Item 1.2.1'],
        5 => ['nodePath' => '1.2.2', 'name' => 'Item 1.2.2'],
        6 => ['nodePath' => '1.3', 'name' => 'Item 1.3'],
        7 => ['nodePath' => '1.3.1', 'name' => 'Item 1.3.1'],
        8 => ['nodePath' => '1.3.2', 'name' => 'Item 1.3.2'],
        9 => ['nodePath' => '1.4', 'name' => 'Item 1.4'],
        10 => ['nodePath' => '1.4.1', 'name' => 'Item 1.4.1'],
        11 => ['nodePath' => '1.4.1.1', 'name' => 'Item 1.4.1.1'],
        12 => ['nodePath' => '1.4.1.2', 'name' => 'Item 1.4.1.2'],
        13 => ['nodePath' => '1.4.2', 'name' => 'Item 1.4.2'],
    ];

    public function _before(): void
    {
        $this->createTable();
        $this->loadFixtures();
    }

    protected function createTable(): void
    {
        $db = Yii::$app->db;
        $tableName = Item::tableName();

        // Drop table if exists
        if ($db->schema->getTableSchema($tableName) !== null) {
            $db->createCommand()->dropTable($tableName)->execute();
        }

        // Create table
        $db->createCommand()->createTable($tableName, [
            'id' => 'INT PRIMARY KEY AUTO_INCREMENT',
            'name' => 'VARCHAR(255)',
            'path' => 'VARCHAR(255) NOT NULL UNIQUE',
            'left' => 'DOUBLE NOT NULL',
            'right' => 'DOUBLE NOT NULL',
            'level' => 'INT NOT NULL',
        ])->execute();

        $db->createCommand()->createIndex('idx-items-left', $tableName, 'left')->execute();
        $db->createCommand()->createIndex('idx-items-right', $tableName, 'right')->execute();
    }

    protected function loadFixtures(): void
    {
        foreach ($this->itemList as $id => $config) {
            $item = new Item();
            $item->id = $id;
            $item->name = $config['name'];
            // setNodePath computes and sets path, left, right, and level
            $item->setNodePath($config['nodePath']);
            $item->save(false);
        }
    }
}
