<?php
/**
 * MatrixHelperCest.php
 *
 * PHP version 8.3+
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */

namespace tests\item;

use blackcube\hazeltree\helpers\MatrixHelper;
use tests\ItemTester;

/**
 * Test MatrixHelper methods
 *
 * @copyright 2010-2025 Philippe Gaultier
 * @license https://www.blackcube.io/license
 * @link https://www.blackcube.io
 */
class MatrixHelperCest
{
    public function testConstructor(ItemTester $I): void
    {
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $I->assertEquals(1, $matrix->a);
        $I->assertEquals(2, $matrix->b);
        $I->assertEquals(3, $matrix->c);
        $I->assertEquals(4, $matrix->d);
    }

    public function testToArray(ItemTester $I): void
    {
        // Test basic conversion
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $I->assertEquals([1, 2, 3, 4], $matrix->toArray());

        // Test with different values
        $matrix = new MatrixHelper([5, 10, 15, 20]);
        $I->assertEquals([5, 10, 15, 20], $matrix->toArray());

        // Test with zeros
        $matrix = new MatrixHelper([0, 1, 1, 0]);
        $I->assertEquals([0, 1, 1, 0], $matrix->toArray());

        // Test with negative values
        $matrix = new MatrixHelper([-1, 2, -3, 4]);
        $I->assertEquals([-1, 2, -3, 4], $matrix->toArray());

        // Test with floats
        $matrix = new MatrixHelper([1.5, 2.5, 3.5, 4.5]);
        $I->assertEquals([1.5, 2.5, 3.5, 4.5], $matrix->toArray());
    }

    public function testTranspose(ItemTester $I): void
    {
        // Matrix:
        // | a  b |    | 1  2 |
        // | c  d | =  | 3  4 |
        //
        // Transpose:
        // | a  c |    | 1  3 |
        // | b  d | =  | 2  4 |
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $matrix->transpose();
        $I->assertEquals(1, $matrix->a);
        $I->assertEquals(3, $matrix->b);
        $I->assertEquals(2, $matrix->c);
        $I->assertEquals(4, $matrix->d);
        $I->assertEquals([1, 3, 2, 4], $matrix->toArray());

        // Test double transpose returns original
        $matrix = new MatrixHelper([5, 6, 7, 8]);
        $original = $matrix->toArray();
        $matrix->transpose();
        $matrix->transpose();
        $I->assertEquals($original, $matrix->toArray());

        // Test symmetric matrix (transpose equals original)
        $matrix = new MatrixHelper([1, 2, 2, 1]);
        $matrix->transpose();
        $I->assertEquals([1, 2, 2, 1], $matrix->toArray());
    }

    public function testGetDeterminant(ItemTester $I): void
    {
        // det([a,b,c,d]) = a*d - b*c
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $I->assertEquals(-2, $matrix->getDeterminant()); // 1*4 - 2*3 = -2

        $matrix = new MatrixHelper([4, 3, 2, 1]);
        $I->assertEquals(-2, $matrix->getDeterminant()); // 4*1 - 3*2 = -2

        // Identity matrix has determinant 1
        $matrix = new MatrixHelper([1, 0, 0, 1]);
        $I->assertEquals(1, $matrix->getDeterminant());

        // Zero determinant (singular matrix)
        $matrix = new MatrixHelper([1, 2, 2, 4]);
        $I->assertEquals(0, $matrix->getDeterminant()); // 1*4 - 2*2 = 0
    }

    public function testMultiplyByScalar(ItemTester $I): void
    {
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $matrix->multiply(2);
        $I->assertEquals([2, 4, 6, 8], $matrix->toArray());

        // Multiply by zero
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $matrix->multiply(0);
        $I->assertEquals([0, 0, 0, 0], $matrix->toArray());

        // Multiply by negative
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $matrix->multiply(-1);
        $I->assertEquals([-1, -2, -3, -4], $matrix->toArray());
    }

    public function testMultiplyByMatrix(ItemTester $I): void
    {
        // [1,2,3,4] * [5,6,7,8]
        // Result:
        // a = 1*5 + 2*7 = 19
        // b = 1*6 + 2*8 = 22
        // c = 3*5 + 4*7 = 43
        // d = 3*6 + 4*8 = 50
        $matrix1 = new MatrixHelper([1, 2, 3, 4]);
        $matrix2 = new MatrixHelper([5, 6, 7, 8]);
        $matrix1->multiply($matrix2);
        $I->assertEquals([19, 22, 43, 50], $matrix1->toArray());

        // Multiply by identity matrix should not change
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $identity = new MatrixHelper([1, 0, 0, 1]);
        $matrix->multiply($identity);
        $I->assertEquals([1, 2, 3, 4], $matrix->toArray());
    }

    public function testAdjugate(ItemTester $I): void
    {
        // Adjugate of [a,b,c,d] = [d,-b,-c,a]
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $matrix->adjugate();
        $I->assertEquals([4, -2, -3, 1], $matrix->toArray());

        $matrix = new MatrixHelper([5, 6, 7, 8]);
        $matrix->adjugate();
        $I->assertEquals([8, -6, -7, 5], $matrix->toArray());
    }

    public function testInverse(ItemTester $I): void
    {
        // Inverse = adjugate / determinant
        // For [1,2,3,4]: det = -2, adj = [4,-2,-3,1]
        // inverse = [-2, 1, 1.5, -0.5]
        $matrix = new MatrixHelper([1, 2, 3, 4]);
        $matrix->inverse();
        $I->assertEquals(-2, $matrix->a);
        $I->assertEquals(1, $matrix->b);
        $I->assertEquals(1.5, $matrix->c);
        $I->assertEquals(-0.5, $matrix->d);

        // A * A^-1 = Identity (verify with multiplication)
        $matrix = new MatrixHelper([2, 1, 1, 1]);
        $original = new MatrixHelper([2, 1, 1, 1]);
        $matrix->inverse();
        $original->multiply($matrix);
        // Should be close to identity [1, 0, 0, 1]
        $I->assertEquals(1, round($original->a, 10));
        $I->assertEquals(0, round($original->b, 10));
        $I->assertEquals(0, round($original->c, 10));
        $I->assertEquals(1, round($original->d, 10));
    }
}
