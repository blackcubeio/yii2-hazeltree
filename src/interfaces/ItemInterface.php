<?php
/**
 * ItemInterface.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace blackcube\hazeltree\interfaces;

use blackcube\hazeltree\helpers\MatrixHelper;
use yii\db\ActiveQuery;

/**
 * Interface for models that can be used with HazelTree nested set
 *
 * Required database columns: path, left, right, level
 *
 * @property string $path
 * @property float $left
 * @property float $right
 * @property int $level
 * @property-read bool $isRoot
 * @property-read MatrixHelper $nodeMatrix
 * @property-read ItemInterface|null $parent
 * @property-read ItemInterface[] $parents
 * @property-read ItemInterface[] $children
 * @property-read ItemInterface[] $siblings
 * @property-read ItemInterface|null $previousSibling
 * @property-read ItemInterface|null $nextSibling
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
interface ItemInterface
{
    /**
     * @return bool
     */
    public function getIsRoot(): bool;

    /**
     * @return ActiveQuery
     */
    public function getChildren(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getTree(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getParents(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getSiblings(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getSiblingsTrees(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getPreviousSiblingsTrees(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getPreviousSiblings(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getPreviousSibling(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getNextSiblingsTrees(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getNextSiblings(): ActiveQuery;

    /**
     * @return ActiveQuery
     */
    public function getNextSibling(): ActiveQuery;

    /**
     * @param string $nodePath
     * @return void
     */
    public function setNodePath(string $nodePath): void;

    /**
     * @param MatrixHelper $matrix
     * @return void
     */
    public function setNodeMatrix(MatrixHelper $matrix): void;

    /**
     * @return MatrixHelper
     */
    public function getNodeMatrix(): MatrixHelper;

    /**
     * @param string $targetPath
     * @return bool
     */
    public function canMove(string $targetPath): bool;

    /**
     * Insert or save current item into target item at last position
     * @param ItemInterface $targetItem
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     */
    public function saveInto(ItemInterface $targetItem, bool $runValidation = true, ?array $attributeNames = null): bool;

    /**
     * Insert or save current item before target item
     * @param ItemInterface $targetItem
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     */
    public function saveBefore(ItemInterface $targetItem, bool $runValidation = true, ?array $attributeNames = null): bool;

    /**
     * Insert or save current item after target item
     * @param ItemInterface $targetItem
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     */
    public function saveAfter(ItemInterface $targetItem, bool $runValidation = true, ?array $attributeNames = null): bool;

    /**
     * Move current item into target item at last position
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveInto(ItemInterface $targetItem): void;

    /**
     * Move current item before target item
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveBefore(ItemInterface $targetItem): void;

    /**
     * Move current item after target item
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveAfter(ItemInterface $targetItem): void;

    /**
     * Refresh the model from the database
     * @return bool
     */
    public function refresh();

    /**
     * Save the model to the database
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     */
    public function save($runValidation = true, $attributeNames = null);

    /**
     * Returns the primary key value
     * @param bool $asArray
     * @return mixed
     */
    public function getPrimaryKey($asArray = false);
}
