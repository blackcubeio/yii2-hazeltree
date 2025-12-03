<?php
/**
 * Item.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests\_support\models;

use blackcube\hazeltree\interfaces\ItemInterface;
use blackcube\hazeltree\traits\ItemTrait;
use yii\db\ActiveRecord;

/**
 * Test model for HazelTree
 *
 * @property int $id
 * @property string $name
 * @property string $path
 * @property float $left
 * @property float $right
 * @property int $level
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class Item extends ActiveRecord implements ItemInterface
{
    use ItemTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%items}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return array_merge(static::treeRules(), [
            [['name'], 'string', 'max' => 255],
        ]);
    }
}
