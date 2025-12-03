<?php
/**
 * ItemBehavior.php
 *
 * PHP Version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace blackcube\hazeltree\behaviors;

use blackcube\hazeltree\exceptions\InvalidNodeConfigurationException;
use blackcube\hazeltree\helpers\MatrixHelper;
use blackcube\hazeltree\helpers\TreeHelper;
use blackcube\hazeltree\interfaces\ItemInterface;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use Yii;

/**
 * HazelTree behavior for ActiveRecord models
 *
 * This behavior implements the HazelTree nested set algorithm based on
 * Dan Hazel's research "Using rational numbers to key nested sets" (2008)
 *
 * @property-read ActiveRecord&ItemInterface $owner
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class ItemBehavior extends Behavior
{
    /**
     * @var MatrixHelper|null node path in matrix notation
     */
    private ?MatrixHelper $nodeMatrix = null;

    /**
     * {@inheritdoc}
     * @throws InvalidConfigException
     */
    public function attach($owner)
    {
        if (!$owner instanceof ActiveRecord) {
            throw new InvalidConfigException('ItemBehavior can only be attached to ActiveRecord instances.');
        }
        if (!$owner instanceof ItemInterface) {
            throw new InvalidConfigException('Owner must implement ItemInterface.');
        }
        /* @var ActiveRecord&ItemInterface $owner */
        parent::attach($owner);
    }

    /**
     * @return bool
     */
    public function getIsRoot(): bool
    {
        return ($this->owner->level === 1);
    }

    /**
     * @return ActiveQuery
     */
    public function getChildren(): ActiveQuery
    {
        $owner = $this->owner;
        $activeQuery = $owner::find()
            ->andWhere(['>', 'left', $owner->left])
            ->andWhere(['<', 'right', $owner->right])
            ->orderBy(['left' => SORT_ASC]);
        $activeQuery->multiple = true;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getTree(): ActiveQuery
    {
        $owner = $this->owner;
        $activeQuery = $owner::find()
            ->andWhere(['>=', 'left', $owner->left])
            ->andWhere(['<=', 'right', $owner->right])
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
            ->andWhere(['level' => ($this->owner->level - 1)]);
        $activeQuery->multiple = false;
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getParents(): ActiveQuery
    {
        $owner = $this->owner;
        $activeQuery = $owner::find()
            ->andWhere(['<', 'left', $owner->left])
            ->andWhere(['>', 'right', $owner->right])
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
            ->andWhere(['level' => $this->owner->level]);
        return $activeQuery;
    }

    /**
     * @return ActiveQuery
     */
    public function getSiblingsTrees(): ActiveQuery
    {
        $owner = $this->owner;
        if ($this->getIsRoot() === true) {
            $activeQuery = $owner::find()
                ->andWhere(['<=', 'right', $owner->left])
                ->andWhere(['>=', 'left', $owner->right])
                ->orderBy(['left' => SORT_ASC]);
        } else {
            /** @var ItemInterface $parent */
            $parent = $this->getParent()->one();
            $activeQuery = $owner::find()
                ->andWhere(['>', 'left', $parent->left])
                ->andWhere(['<', 'right', $parent->right])
                ->andWhere(['or',
                    ['<', 'left', $owner->left],
                    ['>', 'right', $owner->right]
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
        $owner = $this->owner;
        if ($this->getIsRoot() === true) {
            $activeQuery = $owner::find()
                ->andWhere(['<=', 'right', $owner->left])
                ->orderBy(['left' => SORT_ASC]);
        } else {
            /** @var ItemInterface $parent */
            $parent = $this->getParent()->one();
            $activeQuery = $owner::find()
                ->andWhere(['>', 'left', $parent->left])
                ->andWhere(['<', 'right', $parent->right])
                ->andWhere(['<=', 'right', $owner->left])
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
            ->andWhere(['level' => $this->owner->level]);
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
        $owner = $this->owner;
        if ($this->getIsRoot() === true) {
            $activeQuery = $owner::find()
                ->andWhere(['>=', 'left', $owner->right])
                ->orderBy(['left' => SORT_ASC]);
        } else {
            /** @var ItemInterface $parent */
            $parent = $this->getParent()->one();
            $activeQuery = $owner::find()
                ->andWhere(['>', 'left', $parent->left])
                ->andWhere(['<', 'right', $parent->right])
                ->andWhere(['>=', 'left', $owner->right])
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
            ->andWhere(['level' => $this->owner->level]);
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
        $this->owner->path = $nodePath;
        $this->owner->level = TreeHelper::getLevelFromPath($nodePath);
        $this->owner->left = TreeHelper::getLeftFromMatrix($this->nodeMatrix);
        $this->owner->right = TreeHelper::getRightFromMatrix($this->nodeMatrix);
    }

    /**
     * @param MatrixHelper $matrix
     * @return void
     */
    public function setNodeMatrix(MatrixHelper $matrix): void
    {
        $this->nodeMatrix = $matrix;
        $this->owner->path = TreeHelper::convertMatrixToPath($matrix);
        $this->owner->level = TreeHelper::getLevelFromPath($this->owner->path);
        $this->owner->left = TreeHelper::getLeftFromMatrix($this->nodeMatrix);
        $this->owner->right = TreeHelper::getRightFromMatrix($this->nodeMatrix);
    }

    /**
     * @return MatrixHelper
     */
    public function getNodeMatrix(): MatrixHelper
    {
        return TreeHelper::convertPathToMatrix($this->owner->path);
    }

    /**
     * @param string $targetPath
     * @return bool
     */
    public function canMove(string $targetPath): bool
    {
        return (strncmp($this->owner->path, $targetPath, strlen($this->owner->path)) !== 0);
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
        $owner = $this->owner;
        $status = false;
        
        if ($owner->getIsNewRecord() === false) {
            $transaction = $owner::getDb()->beginTransaction();
            $status = $owner->save($runValidation, $attributeNames);
            if ($status === true) {
                $this->moveInto($targetItem);
            }
            if ($owner->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        } elseif ($owner->path !== null) {
            throw new InvalidNodeConfigurationException(Yii::t('hazeltree/models/node', 'Cannot "saveInto()" a new record with a node path'));
        } else {
            $transaction = $owner::getDb()->beginTransaction();
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
            $status = $owner->save($runValidation, $attributeNames);
            if ($owner->hasErrors() === true) {
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
        $owner = $this->owner;
        $status = false;
        
        if ($owner->getIsNewRecord() === false) {
            $transaction = $owner::getDb()->beginTransaction();
            $status = $owner->save($runValidation, $attributeNames);
            if ($status === true) {
                $this->moveBefore($targetItem);
            }
            if ($owner->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        } elseif ($owner->path !== null) {
            throw new InvalidNodeConfigurationException(Yii::t('hazeltree/models/node', 'Cannot "saveBefore()" a new record with a node path'));
        } else {
            $transaction = $owner::getDb()->beginTransaction();
            $path = $targetItem->path;

            $nodesMoveMatrix = $this->prepareMoveMatrix($targetItem, $targetItem, 1);

            $nodesToMove = $this->getItemAndSiblings($targetItem)
                ->orderBy(['left' => SORT_DESC]);

            $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);

            $this->setNodePath($path);
            $status = $owner->save($runValidation, $attributeNames);
            if ($owner->hasErrors() === true) {
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
        $owner = $this->owner;
        $status = false;
        
        if ($owner->getIsNewRecord() === false) {
            $transaction = $owner::getDb()->beginTransaction();
            $status = $owner->save($runValidation, $attributeNames);
            if ($status === true) {
                $this->moveAfter($targetItem);
            }
            if ($owner->hasErrors() === true) {
                $transaction->rollBack();
            } else {
                $transaction->commit();
            }
        } elseif ($owner->path !== null) {
            throw new InvalidNodeConfigurationException(Yii::t('hazeltree/models/node', 'Cannot "saveAfter()" a new record with a node path'));
        } else {
            $transaction = $owner::getDb()->beginTransaction();
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
            $status = $owner->save($runValidation, $attributeNames);
            if ($owner->hasErrors() === true) {
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
        $owner = $this->owner;
        
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
                $nodesMoveMatrix = $this->prepareMoveMatrix($owner, $targetChild, $bump);
            } else {
                $bump = 1 - $currentLastSegment;
                $nodesMoveMatrix = $this->prepareMoveMatrix($owner, $targetItem, $bump, true);
            }

            /** @var ItemInterface|null $nextSibling */
            $nextSibling = $this->getNextSibling()->one();
            $nextSiblingPk = ($nextSibling !== null) ? $nextSibling->getPrimaryKey() : null;

            $transaction = $owner::getDb()->beginTransaction();

            $nodesToMove = $this->getTree();

            $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);

            if ($nextSiblingPk !== null) {
                $targetItem->refresh();
                $owner->refresh();
                $this->moveBackItems($nextSiblingPk);
            }

            $transaction->commit();
            $owner->refresh();
        }
    }

    /**
     * Move current item before target item
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveBefore(ItemInterface $targetItem): void
    {
        $owner = $this->owner;
        
        if ($this->canMove($targetItem->path) === true) {
            /** @var ItemInterface|null $nextSibling */
            $nextSibling = $this->getNextSibling()->one();
            $nextSiblingPk = ($nextSibling !== null) ? $nextSibling->getPrimaryKey() : null;

            $transaction = $owner::getDb()->beginTransaction();

            $nodesMoveMatrix = $this->prepareMoveMatrix($targetItem, $targetItem, 1);

            $nodesToMove = $this->getItemAndSiblings($targetItem)->orderBy(['left' => SORT_DESC]);

            $this->moveAndSaveItems($nodesToMove, $nodesMoveMatrix);

            $targetItem->refresh();
            $owner->refresh();

            $this->moveThisItemTree($targetItem, true);

            $targetItem->refresh();
            $owner->refresh();
            $this->moveBackItems($nextSiblingPk);

            $transaction->commit();
            $targetItem->refresh();
            $owner->refresh();
        }
    }

    /**
     * Move current item after target item
     * @param ItemInterface $targetItem
     * @return void
     */
    public function moveAfter(ItemInterface $targetItem): void
    {
        $owner = $this->owner;
        
        if ($this->canMove($targetItem->path) === true) {
            /** @var ItemInterface|null $targetItemNextSibling */
            $targetItemNextSibling = $targetItem->getNextSibling()->one();
            if ($targetItemNextSibling !== null) {
                $this->moveBefore($targetItemNextSibling);
            } else {
                $transaction = $owner::getDb()->beginTransaction();
                /** @var ItemInterface|null $nextSibling */
                $nextSibling = $this->getNextSibling()->one();
                $nextSiblingPk = ($nextSibling !== null) ? $nextSibling->getPrimaryKey() : null;

                $this->moveThisItemTree($targetItem, false);

                $owner->refresh();
                $this->moveBackItems($nextSiblingPk);

                $transaction->commit();
                $targetItem->refresh();
                $owner->refresh();
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
        $owner = $this->owner;
        
        $itemLastSegment = TreeHelper::getLastSegment($this->getNodeMatrix());
        $targetLastSegment = TreeHelper::getLastSegment($targetItem->getNodeMatrix());
        if ($moveBefore === true) {
            $itemBump = $targetLastSegment - $itemLastSegment - 1;
        } else {
            $itemBump = $targetLastSegment - $itemLastSegment + 1;
        }

        $nodesMoveMatrix = $this->prepareMoveMatrix($owner, $targetItem, $itemBump);

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
            $owner = $this->owner;
            /** @var ItemInterface|null $nextSibling */
            $nextSibling = $owner::findOne($itemPk);
            
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
        $owner = $this->owner;
        $pk = $owner::primaryKey();
        $treeQuery = $item->getTree()->select($pk);
        $siblingsTreeQuery = $item->getNextSiblingsTrees()->select($pk);
        return $owner::find()
            ->where(['in', $pk, $treeQuery])
            ->orWhere(['in', $pk, $siblingsTreeQuery]);
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
    public static function rules(): array
    {
        return [
            [['left', 'right'], 'number'],
            [['level'], 'integer'],
            [['path'], 'string', 'max' => 255],
            [['path'], 'unique'],
        ];
    }
}
