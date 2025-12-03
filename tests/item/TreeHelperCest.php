<?php
/**
 * TreeHelperCest.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests\item;

use blackcube\hazeltree\helpers\MatrixHelper;
use blackcube\hazeltree\helpers\TreeHelper;
use tests\ItemTester;

/**
 * Test TreeHelper methods
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class TreeHelperCest
{
    public function testGetLastSegmentWithString(ItemTester $I): void
    {
        // Test with simple path
        $I->assertEquals(1, TreeHelper::getLastSegment('1'));

        // Test with nested path
        $I->assertEquals(2, TreeHelper::getLastSegment('1.2'));
        $I->assertEquals(3, TreeHelper::getLastSegment('1.2.3'));
        $I->assertEquals(4, TreeHelper::getLastSegment('1.2.3.4'));

        // Test with larger segment numbers
        $I->assertEquals(10, TreeHelper::getLastSegment('1.10'));
        $I->assertEquals(99, TreeHelper::getLastSegment('1.2.99'));
    }

    public function testGetLastSegmentWithMatrix(ItemTester $I): void
    {
        // Test with matrix (path 1)
        $matrix = TreeHelper::convertPathToMatrix('1');
        $I->assertEquals(1, TreeHelper::getLastSegment($matrix));

        // Test with matrix (path 1.2)
        $matrix = TreeHelper::convertPathToMatrix('1.2');
        $I->assertEquals(2, TreeHelper::getLastSegment($matrix));

        // Test with matrix (path 1.2.3)
        $matrix = TreeHelper::convertPathToMatrix('1.2.3');
        $I->assertEquals(3, TreeHelper::getLastSegment($matrix));
    }

    public function testGetBasePathWithString(ItemTester $I): void
    {
        // Test with simple path - should return empty string
        $I->assertEquals('', TreeHelper::getBasePath('1'));

        // Test with nested paths
        $I->assertEquals('1', TreeHelper::getBasePath('1.2'));
        $I->assertEquals('1.2', TreeHelper::getBasePath('1.2.3'));
        $I->assertEquals('1.2.3', TreeHelper::getBasePath('1.2.3.4'));

        // Test with larger segment numbers
        $I->assertEquals('1', TreeHelper::getBasePath('1.10'));
        $I->assertEquals('1.2', TreeHelper::getBasePath('1.2.99'));
    }

    public function testGetBasePathWithMatrix(ItemTester $I): void
    {
        // Test with matrix (path 1) - should return empty string
        $matrix = TreeHelper::convertPathToMatrix('1');
        $I->assertEquals('', TreeHelper::getBasePath($matrix));

        // Test with matrix (path 1.2)
        $matrix = TreeHelper::convertPathToMatrix('1.2');
        $I->assertEquals('1', TreeHelper::getBasePath($matrix));

        // Test with matrix (path 1.2.3)
        $matrix = TreeHelper::convertPathToMatrix('1.2.3');
        $I->assertEquals('1.2', TreeHelper::getBasePath($matrix));

        // Test with matrix (path 1.2.3.4)
        $matrix = TreeHelper::convertPathToMatrix('1.2.3.4');
        $I->assertEquals('1.2.3', TreeHelper::getBasePath($matrix));
    }

    public function testConvertPathToMatrix(ItemTester $I): void
    {
        // Test conversion and back
        $path = '1.2.3';
        $matrix = TreeHelper::convertPathToMatrix($path);
        $I->assertInstanceOf(MatrixHelper::class, $matrix);
        $I->assertEquals($path, TreeHelper::convertMatrixToPath($matrix));

        // Test with different paths
        $paths = ['1', '1.1', '1.2.3.4.5', '2', '2.1.1'];
        foreach ($paths as $testPath) {
            $matrix = TreeHelper::convertPathToMatrix($testPath);
            $I->assertEquals($testPath, TreeHelper::convertMatrixToPath($matrix));
        }
    }

    public function testGetLevelFromPath(ItemTester $I): void
    {
        $I->assertEquals(1, TreeHelper::getLevelFromPath('1'));
        $I->assertEquals(2, TreeHelper::getLevelFromPath('1.2'));
        $I->assertEquals(3, TreeHelper::getLevelFromPath('1.2.3'));
        $I->assertEquals(4, TreeHelper::getLevelFromPath('1.2.3.4'));
        $I->assertEquals(5, TreeHelper::getLevelFromPath('1.2.3.4.5'));
    }

    public function testGetLevelFromMatrix(ItemTester $I): void
    {
        // Test level 1
        $matrix = TreeHelper::convertPathToMatrix('1');
        $I->assertEquals(1, TreeHelper::getLevelFromMatrix($matrix));

        // Test level 2
        $matrix = TreeHelper::convertPathToMatrix('1.2');
        $I->assertEquals(2, TreeHelper::getLevelFromMatrix($matrix));

        // Test level 3
        $matrix = TreeHelper::convertPathToMatrix('1.2.3');
        $I->assertEquals(3, TreeHelper::getLevelFromMatrix($matrix));

        // Test level 4
        $matrix = TreeHelper::convertPathToMatrix('1.2.3.4');
        $I->assertEquals(4, TreeHelper::getLevelFromMatrix($matrix));

        // Test level 5
        $matrix = TreeHelper::convertPathToMatrix('1.2.3.4.5');
        $I->assertEquals(5, TreeHelper::getLevelFromMatrix($matrix));

        // Verify consistency with getLevelFromPath
        $paths = ['1', '2', '1.1', '1.2.3', '1.2.3.4.5.6'];
        foreach ($paths as $path) {
            $matrix = TreeHelper::convertPathToMatrix($path);
            $I->assertEquals(
                TreeHelper::getLevelFromPath($path),
                TreeHelper::getLevelFromMatrix($matrix),
                "Level should be same for path '$path' whether computed from path or matrix"
            );
        }
    }

    public function testGetLeftRightFromMatrix(ItemTester $I): void
    {
        // Test path 1: left = a/c = 1/1 = 1, right = b/d = 2/1 = 2
        $matrix = TreeHelper::convertPathToMatrix('1');
        $I->assertEquals(1, TreeHelper::getLeftFromMatrix($matrix));
        $I->assertEquals(2, TreeHelper::getRightFromMatrix($matrix));

        // Verify left < right for nested paths (nested set property)
        $paths = ['1', '1.1', '1.2', '1.2.1', '1.2.3.4'];
        foreach ($paths as $path) {
            $matrix = TreeHelper::convertPathToMatrix($path);
            $left = TreeHelper::getLeftFromMatrix($matrix);
            $right = TreeHelper::getRightFromMatrix($matrix);
            $I->assertLessThan($right, $left, "For path $path: left ($left) should be < right ($right)");
        }
    }
}
