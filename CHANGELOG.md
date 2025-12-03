Yii2 Hazel Tree Change Log
==========================

1.0.0 December 3, 2025
----------------------

* Initial public release
* Nested set implementation using Dan Hazel's rational numbers algorithm
* ItemTrait for easy integration with ActiveRecord models
* Support for composite primary keys
* Transaction-safe operations with automatic rollback
* Tree traversal: children, parents, siblings, tree
* Node operations: saveInto, saveBefore, saveAfter, moveInto, moveBefore, moveAfter
* Matrix-based path encoding using 2x2 matrices
* Path-based addressing with dot notation (e.g., "1.2.3")
