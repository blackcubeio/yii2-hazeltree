<?php
/**
 * CompositeItem.php
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
 * Test model for HazelTree with composite primary key
 *
 * @property int $type_id
 * @property int $item_id
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
class CompositeItem extends ActiveRecord implements ItemInterface
{
    use ItemTrait;

    /**
     * {@inheritdoc}
     */
    public static function tableName(): string
    {
        return '{{%composite_items}}';
    }

    /**
     * {@inheritdoc}
     */
    public static function primaryKey(): array
    {
        return ['type_id', 'item_id'];
    }

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return array_merge(static::treeRules(), [
            [['type_id', 'item_id'], 'integer'],
            [['type_id', 'item_id'], 'required'],
            [['name'], 'string', 'max' => 255],
        ]);
    }
}
