Yii2 Hazel Tree
===============

High-performance nested set implementation for Yii2 using rational numbers.

## Why Hazel Tree?

Hazel Tree uses **rational fractions** (2x2 matrix encoding) instead of integers for `left`/`right` boundaries.

- **Optimal reads**: Single SQL query to retrieve descendants, ancestors or siblings
- **Slightly degraded writes**: Insert/move operations are more expensive than a simple `parent_id`

Since tree structures involve far more reads than writes (navigation, menus, breadcrumbs...), it's a net win.

## Attribution

> Hazel Tree is an implementation of **Dan Hazel**'s research published in 2008: "Using rational numbers to key nested sets". This innovative approach solves the limitations of traditional Nested Sets by using rational fractions instead of integers, making insert operations much more efficient.
>
> [Read the original paper on arXiv](https://arxiv.org/abs/0806.3115)

## Features

- Single SQL query for tree traversal (children, parents, siblings)
- No rebalancing needed on insert/move operations
- Rational number boundaries using 2x2 matrix encoding
- Path-based addressing with dot notation (e.g., "1.2.3")

## Installation

```bash
composer require blackcube/yii2-hazeltree
```

## Usage

### 1. Create your model

Your model must:
- Extend `ActiveRecord`
- Implement `ItemInterface`
- Attach `ItemBehavior`
- Have columns: `path`, `left`, `right`, `level`

```php
<?php

namespace app\models;

use blackcube\hazeltree\behaviors\ItemBehavior;
use blackcube\hazeltree\interfaces\ItemInterface;
use yii\db\ActiveRecord;

class Category extends ActiveRecord implements ItemInterface
{
    public function behaviors()
    {
        return [
            'hazeltree' => ItemBehavior::class,
        ];
    }

    public static function tableName()
    {
        return '{{%categories}}';
    }

    public function rules()
    {
        return array_merge(ItemBehavior::rules(), [
            // your own rules
        ]);
    }
}
```

### 2. Database migration

Required columns for HazelTree:

```php
$this->createTable('{{%categories}}', [
    // Required by HazelTree
    'path' => $this->string(255)->notNull()->unique(),
    'left' => $this->double()->notNull(),
    'right' => $this->double()->notNull(),
    'level' => $this->integer()->notNull(),

    // Your own columns
    'id' => $this->primaryKey(),
    'name' => $this->string(255),
    // ...
]);

$this->createIndex('idx-categories-left', '{{%categories}}', 'left');
$this->createIndex('idx-categories-right', '{{%categories}}', 'right');
```

### 3. Tree operations

```php
// Create root node
$root = new Category();
$root->name = 'Root';
$root->setNodePath('1');
$root->save();

// Insert as last child
$child = new Category();
$child->name = 'Child';
$child->saveInto($root);

// Insert before sibling
$another = new Category();
$another->name = 'Another';
$another->saveBefore($child);

// Insert after sibling
$last = new Category();
$last->name = 'Last';
$last->saveAfter($child);

// Move existing node
$child->moveInto($another);
$child->moveBefore($last);
$child->moveAfter($root);
```

### 4. Traversal

```php
// Get children (one level)
$children = $category->getChildren()
    ->andWhere(['level' => $category->level + 1])
    ->all();

// Get all descendants
$descendants = $category->children;

// Get parent
$parent = $category->parent;

// Get all ancestors
$ancestors = $category->parents;

// Get siblings
$siblings = $category->siblings;

// Get previous/next sibling
$prev = $category->previousSibling;
$next = $category->nextSibling;

// Get full subtree (including self)
$tree = $category->tree;

// Check if root
if ($category->isRoot) {
    // ...
}
```

## License

BSD-3-Clause