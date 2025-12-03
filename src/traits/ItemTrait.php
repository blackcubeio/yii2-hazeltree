<?php
/**
 * ItemTrait.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace blackcube\hazeltree\traits;

use blackcube\hazeltree\exceptions\InvalidNodeConfigurationException;
use blackcube\hazeltree\helpers\MatrixHelper;
use blackcube\hazeltree\helpers\TreeHelper;
use blackcube\hazeltree\interfaces\ItemInterface;
use yii\db\ActiveQuery;

/**
 * Trait implementing the HazelTree nested set algorithm
 *
 * This trait implements Dan Hazel's research "Using rational numbers to key nested sets" (2008)
 *
 * Use this trait in any ActiveRecord model that implements ItemInterface.
 *
 * @property string $path
 * @property float $left
 * @property float $right
 * @property int $level
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
trait ItemTrait
{
    /**
     * @var MatrixHelper|null node path in matrix notation
     */
    private ?MatrixHelper $nodeMatrix = null;

    /**
     * @return bool
     */
    public function getIsRoot(): bool
    {
        return ($this->level === 1);
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren(): ActiveQuery
    {
        $activeQuery = static::find()
            ->andWhere(['>', 'left', $this->left])
            ->andWhere(['<', 'right', $this->right])
            ->orderBy(['left' => SORT_ASC]);
        $activeQuery->multiple = true;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getTree(): ActiveQuery
    {
        $activeQuery = static::find()
            ->andWhere(['>=', 'left', $this->left])
            ->andWhere(['<=', 'right', $this->right])
            ->orderBy(['left' => SORT_ASC]);
        $activeQuery->multiple = true;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getParent(): ActiveQuery
    {
        $activeQuery = $this
            ->getParents()
            ->andWhere(['level' => ($this->level - 1)]);
        $activeQuery->multiple = false;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getParents(): ActiveQuery
    {
        $activeQuery = static::find()
            ->andWhere(['<', 'left', $this->left])
            ->andWhere(['>', 'right', $this->right])
            ->orderBy(['left' => SORT_ASC]);
        $activeQuery->multiple = true;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getSiblings(): ActiveQuery
    {
        $activeQuery = $this
            ->getSiblingsTrees()
            ->andWhere(['level' => $this->level]);
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getSiblingsTrees(): ActiveQuery
    {
        if ($this->getIsRoot() === true) {
            $activeQuery = static::find()
                ->andWhere(['<=', 'right', $this->left])
                ->andWhere(['>=', 'left', $this->right])
                ->orderBy(['left' => SORT_ASC]);
        } else {
            /** @var ItemInterface $parent */
            $parent = $this->getParent()->one();
            $activeQuery = static::find()
                ->andWhere(['>', 'left', $parent->left])
                ->andWhere(['<', 'right', $parent->right])
                ->andWhere(['or',
                    ['<', 'left', $this->left],
                    ['>', 'right', $this->right]
                ])
                ->orderBy(['left' => SORT_ASC]);
        }
        $activeQuery->multiple = true;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getPreviousSiblingsTrees(): ActiveQuery
    {
        if ($this->getIsRoot() === true) {
            $activeQuery = static::find()
                ->andWhere(['<=', 'right', $this->left])
                ->orderBy(['left' => SORT_ASC]);
        } else {
            /** @var ItemInterface $parent */
            $parent = $this->getParent()->one();
            $activeQuery = static::find()
                ->andWhere(['>', 'left', $parent->left])
                ->andWhere(['<', 'right', $parent->right])
                ->andWhere(['<=', 'right', $this->left])
                ->orderBy(['left' => SORT_ASC]);
        }
        $activeQuery->multiple = true;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getPreviousSiblings(): ActiveQuery
    {
        $activeQuery = $this->getPreviousSiblingsTrees();
        $activeQuery
            ->andWhere(['level' => $this->level]);
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getPreviousSibling(): ActiveQuery
    {
        $activeQuery = $this
            ->getPreviousSiblings()
            ->orderBy(['left' => SORT_DESC]);
        $activeQuery->multiple = false;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getNextSiblingsTrees(): ActiveQuery
    {
        if ($this->getIsRoot() === true) {
            $activeQuery = static::find()
                ->andWhere(['>=', 'left', $this->right])
                ->orderBy(['left' => SORT_ASC]);
        } else {
            /** @var ItemInterface $parent */
            $parent = $this->getParent()->one();
            $activeQuery = static::find()
                ->andWhere(['>', 'left', $parent->left])
                ->andWhere(['<', 'right', $parent->right])
                ->andWhere(['>=', 'left', $this->right])
                ->orderBy(['left' => SORT_ASC]);
        }
        $activeQuery->multiple = true;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getNextSiblings(): ActiveQuery
    {
        $activeQuery = $this
            ->getNextSiblingsTrees();
        $activeQuery
            ->andWhere(['level' => $this->level]);
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getNextSibling(): ActiveQuery
    {
        $activeQuery = $this
            ->getNextSiblings();
        $activeQuery->limit(1);
        $activeQuery->multiple = false;
        return $activeQuery;
    }

    /**
     * @param string $nodePath
     * @return void
     */
    public function setNodePath(string $nodePath): void
    {
        $this->nodeMatrix = TreeHelper::convertPathToMatrix($nodePath);
        $this->path = $nodePath;
        $this->level = TreeHelper::getLevelFromPath($nodePath);
        $this->left = TreeHelper::getLeftFromMatrix($this->nodeMatrix);
        $this->right = TreeHelper::getRightFromMatrix($this->nodeMatrix);
    }

    /**
     * @param MatrixHelper $matrix
     * @return void
     */
    public function setNodeMatrix(MatrixHelper $matrix): void
    {
        $this->nodeMatrix = $matrix;
        $this->path = TreeHelper::convertMatrixToPath($matrix);
        $this->level = TreeHelper::getLevelFromPath($this->path);
        $this->left = TreeHelper::getLeftFromMatrix($this->nodeMatrix);
        $this->right = TreeHelper::getRightFromMatrix($this->nodeMatrix);
    }

    /**
     * @return MatrixHelper
     */
    public function getNodeMatrix(): MatrixHelper
    {
        return TreeHelper::convertPathToMatrix($this->path);
    }

    /**
     * @param string $targetPath
     * @return bool
     */
    public function canMove(string $targetPath): bool
    {
        return (strncmp($this->path, $targetPath, strlen($this->path)) !== 0);
    }

    /**
     * Insert or save current item into target item at last position
     * @param ItemInterface $targetItem
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     * @throws InvalidNodeConfigurationException
     */
    public function saveInto(ItemInterface $targetItem, bool $runValidation = true, ?array $attributeNames = null): bool
    {
        $status = false;

        if ($this->getIsNewRecord() === false) {
            $transaction = static::getDb()->beginTransaction();
            $status = $this->save($runValidation, $attributeNames);
            if ($status === true) {
                $this->moveInto($targetItem);
            }
            if ($this->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        } elseif ($this->path !== null) {
            throw new InvalidNodeConfigurationException('Cannot "saveInto()" a new record with a node path');
        } else {
            $transaction = static::getDb()->beginTransaction();
            /** @var ItemInterface|null $lastChild */
            $lastChild = $targetItem->getChildren()
                ->andWhere(['level' => $targetItem->level + 1])
                ->orderBy(['left' => SORT_DESC])
                ->one();
            if ($lastChild === null) {
                $lastSegment = 1;
            } else {
                $lastSegment = TreeHelper::getLastSegment($lastChild->getNodeMatrix()) + 1;
            }
            $this->setNodePath($targetItem->path . TreeHelper::PATH_SEPARATOR . $lastSegment);
            $status = $this->save($runValidation, $attributeNames);
            if ($this->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        }
        return $status;
    }

    /**
     * Insert or save current item before target item
     * @param ItemInterface $targetItem
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     * @throws InvalidNodeConfigurationException
     */
    public function saveBefore(ItemInterface $targetItem, bool $runValidation = true, ?array $attributeNames = null): bool
    {
        $status = false;

        if ($this->getIsNewRecord() === false) {
            $transaction = static::getDb()->beginTransaction();
            $status = $this->save($runValidation, $attributeNames);
            if ($status === true) {
                $this->moveBefore($targetItem);
            }
            if ($this->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        } elseif ($this->path !== null) {
            throw new InvalidNodeConfigurationException('Cannot "saveBefore()" a new record with a node path');
        } else {
            $transaction = static::getDb()->beginTransaction();
            $path = $targetItem->path;

            $nodesMoveMatrix = $this->prepareMoveMatrix($targetItem, $targetItem, 1);

            $nodesToMove = $this->getItemAndSiblings($targetItem)
                ->orderBy(['left' => SORT_DESC]);

            $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);

            $this->setNodePath($path);
            $status = $this->save($runValidation, $attributeNames);
            if ($this->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        }
        return $status;
    }

    /**
     * Insert or save current item after target item
     * @param ItemInterface $targetItem
     * @param bool $runValidation
     * @param array|null $attributeNames
     * @return bool
     * @throws InvalidNodeConfigurationException
     */
    public function saveAfter(ItemInterface $targetItem, bool $runValidation = true, ?array $attributeNames = null): bool
    {
        $status = false;

        if ($this->getIsNewRecord() === false) {
            $transaction = static::getDb()->beginTransaction();
            $status = $this->save($runValidation, $attributeNames);
            if ($status === true) {
                $this->moveAfter($targetItem);
            }
            if ($this->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        } elseif ($this->path !== null) {
            throw new InvalidNodeConfigurationException('Cannot "saveAfter()" a new record with a node path');
        } else {
            $transaction = static::getDb()->beginTransaction();
            /** @var ItemInterface|null $nextSiblingItem */
            $nextSiblingItem = $targetItem->getNextSibling()->one();
            if ($nextSiblingItem !== null) {
                $path = $nextSiblingItem->path;

                $nodesMoveMatrix = $this->prepareMoveMatrix($targetItem, $targetItem, 1);

                $nodesToMove = $targetItem->getNextSiblingsTrees()
                    ->orderBy(['left' => SORT_DESC]);

                $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);
            } else {
                $parts = explode(TreeHelper::PATH_SEPARATOR, $targetItem->path);
                $lastSegment = array_pop($parts);
                $parts[] = ($lastSegment + 1);
                $path = implode(TreeHelper::PATH_SEPARATOR, $parts);
            }

            $this->setNodePath($path);
            $status = $this->save($runValidation, $attributeNames);
            if ($this->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        }
        return $status;
    }

    /**
     * Move current item into target item at last position
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveInto(ItemInterface $targetItem): void
    {
        if ($this->canMove($targetItem->path) === true) {
            $currentLastSegment = TreeHelper::getLastSegment($this->getNodeMatrix());
            /** @var ItemInterface|null $targetChild */
            $targetChild = $targetItem->getTree()
                ->andWhere(['level' => ($targetItem->level + 1)])
                ->orderBy(['left' => SORT_DESC])
                ->one();
            if ($targetChild !== null) {
                $lastSegment = TreeHelper::getLastSegment($targetChild->getNodeMatrix());
                $bump = ($lastSegment + 1) - $currentLastSegment;
                $nodesMoveMatrix = $this->prepareMoveMatrix($this, $targetChild, $bump);
            } else {
                $bump = 1 - $currentLastSegment;
                $nodesMoveMatrix = $this->prepareMoveMatrix($this, $targetItem, $bump, true);
            }

            /** @var ItemInterface|null $nextSibling */
            $nextSibling = $this->getNextSibling()->one();
            $nextSiblingPk = ($nextSibling !== null) ? $nextSibling->getPrimaryKey() : null;

            $transaction = static::getDb()->beginTransaction();

            $nodesToMove = $this->getTree();

            $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);

            // Only move back siblings if the next sibling is not the target itself
            // (when moving INTO the next sibling, it becomes the parent, not a gap to fill)
            if ($nextSiblingPk !== null && $nextSiblingPk !== $targetItem->getPrimaryKey()) {
                $targetItem->refresh();
                $this->refresh();
                $this->moveBackItems($nextSiblingPk);
            }

            $transaction->commit();
            $this->refresh();
        }
    }

    /**
     * Move current item before target item
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveBefore(ItemInterface $targetItem): void
    {
        if ($this->canMove($targetItem->path) === true) {
            /** @var ItemInterface|null $nextSibling */
            $nextSibling = $this->getNextSibling()->one();
            $nextSiblingPk = ($nextSibling !== null) ? $nextSibling->getPrimaryKey() : null;

            $transaction = static::getDb()->beginTransaction();

            $nodesMoveMatrix = $this->prepareMoveMatrix($targetItem, $targetItem, 1);

            $nodesToMove = $this->getItemAndSiblings($targetItem)->orderBy(['left' => SORT_DESC]);

            $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);

            $targetItem->refresh();
            $this->refresh();

            $this->moveThisItemTree($targetItem, true);

            $targetItem->refresh();
            $this->refresh();
            $this->moveBackItems($nextSiblingPk);

            $transaction->commit();
            $targetItem->refresh();
            $this->refresh();
        }
    }

    /**
     * Move current item after target item
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveAfter(ItemInterface $targetItem): void
    {
        if ($this->canMove($targetItem->path) === true) {
            /** @var ItemInterface|null $targetItemNextSibling */
            $targetItemNextSibling = $targetItem->getNextSibling()->one();
            if ($targetItemNextSibling !== null) {
                $this->moveBefore($targetItemNextSibling);
            } else {
                $transaction = static::getDb()->beginTransaction();
                /** @var ItemInterface|null $nextSibling */
                $nextSibling = $this->getNextSibling()->one();
                $nextSiblingPk = ($nextSibling !== null) ? $nextSibling->getPrimaryKey() : null;

                $this->moveThisItemTree($targetItem, false);

                $this->refresh();
                $this->moveBackItems($nextSiblingPk);

                $transaction->commit();
                $targetItem->refresh();
                $this->refresh();
            }
        }
    }

    /**
     * @param ItemInterface $targetItem
     * @param bool $moveBefore
     * @return void
     */
    private function moveThisItemTree(ItemInterface $targetItem, bool $moveBefore): void
    {
        $itemLastSegment = TreeHelper::getLastSegment($this->getNodeMatrix());
        $targetLastSegment = TreeHelper::getLastSegment($targetItem->getNodeMatrix());
        if ($moveBefore === true) {
            $itemBump = $targetLastSegment - $itemLastSegment - 1;
        } else {
            $itemBump = $targetLastSegment - $itemLastSegment + 1;
        }

        $nodesMoveMatrix = $this->prepareMoveMatrix($this, $targetItem, $itemBump);

        $nodesToMove = $this->getTree();

        $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);
    }

    /**
     * @param mixed $itemPk
     * @return void
     */
    private function moveBackItems(mixed $itemPk = null): void
    {
        if ($itemPk !== null) {
            /** @var ItemInterface|null $nextSibling */
            $nextSibling = static::findOne($itemPk);

            if ($nextSibling !== null) {
                $nodesMoveMatrix = $this->prepareMoveMatrix($nextSibling, $nextSibling, -1);

                $nodesToMove = $this->getItemAndSiblings($nextSibling)->orderBy(['left' => SORT_ASC]);

                $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);
            }
        }
    }

    /**
     * @param ItemInterface $item
     * @return ActiveQuery
     */
    private function getItemAndSiblings(ItemInterface $item): ActiveQuery
    {
        $pk = static::primaryKey();
        // Fetch IDs first to avoid SQLite subquery limitation
        $treeIds = $item->getTree()->select($pk)->column();
        $siblingsTreeIds = $item->getNextSiblingsTrees()->select($pk)->column();
        $allIds = array_merge($treeIds, $siblingsTreeIds);
        return static::find()
            ->where(['in', $pk, $allIds]);
    }

    /**
     * @param ActiveQuery $itemsToMove
     * @param MatrixHelper $moveMatrix
     * @return void
     */
    private function moveAndSaveItems(ActiveQuery $itemsToMove, MatrixHelper $moveMatrix): void
    {
        /** @var ItemInterface $itemToMove */
        foreach ($itemsToMove->each() as $itemToMove) {
            $childMatrix = $itemToMove->getNodeMatrix();
            $itemMoveMatrix = clone $moveMatrix;
            $itemMoveMatrix->multiply($childMatrix);
            $itemToMove->setNodeMatrix($itemMoveMatrix);
            $itemToMove->save(false, ['path', 'left', 'right', 'level']);
        }
    }

    /**
     * @param ItemInterface $fromItem
     * @param ItemInterface $toItem
     * @param int $bump
     * @param bool $inside
     * @return MatrixHelper
     */
    private function prepareMoveMatrix(ItemInterface $fromItem, ItemInterface $toItem, int $bump, bool $inside = false): MatrixHelper
    {
        $fromMatrix = TreeHelper::extractParentMatrixFromMatrix($fromItem->getNodeMatrix());
        if ($inside === true) {
            $toMatrix = $toItem->getNodeMatrix();
        } else {
            $toMatrix = TreeHelper::extractParentMatrixFromMatrix($toItem->getNodeMatrix());
        }
        return TreeHelper::buildMoveMatrix($fromMatrix, $toMatrix, $bump);
    }

    /**
     * Returns the validation rules for HazelTree attributes
     * These should be merged with the model's own rules
     * @return array
     */
    public static function treeRules(): array
    {
        return [
            [['left', 'right'], 'number'],
            [['level'], 'integer'],
            [['path'], 'string', 'max' => 255],
            [['path'], 'unique'],
        ];
    }
}
