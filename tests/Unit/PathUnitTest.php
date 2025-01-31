<?php

namespace CodeDistortion\Path\Tests\Unit;

use CodeDistortion\Path\AbstractPath;
use CodeDistortion\Path\Path;
use CodeDistortion\Path\PathImmutable;
use CodeDistortion\Path\Tests\PHPUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test the Path and PathImmutable classes.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class PathUnitTest extends PHPUnitTestCase
{
    /**
     * Test that Path breaks down paths correctly.
     *
     * @test
     * @dataProvider pathsDataProvider
     *
     * @param string      $inputPath The path to parse.
     * @param string      $expected  The expected result.
     * @param string      $dir       The expected directory.
     * @param string|null $filename  The expected filename.
     * @return void
     */
    #[Test]
    #[DataProvider('pathsDataProvider')]
    public function test_that_parsed_paths_come_out_the_same(
        string $inputPath,
        string $expected,
        string $dir,
        ?string $filename,
    ): void {

        $path = Path::new($inputPath);
        self::assertSame($expected, (string) $path);
        self::assertSame($dir, (string) $path->getDir());
        self::assertSame($filename, $path->getFilename());
        self::assertSame((string) $path, (string) $path->copy()); // test that ->copy() works
    }

    /**
     * DataProvider for test_that_parsed_paths_come_out_the_same().
     *
     * @return list<array{inputPath: string, expected: string, dir: string, filename: string|null}>
     */
    public static function pathsDataProvider(): array
    {
        /** @var array<array{inputPath: string, expected: string, dir: string, filename: string|null}> $return */
        $return = [
            ['inputPath' => '', 'expected' => '', 'dir' => '', 'filename' => null],
            ['inputPath' => '/', 'expected' => '/', 'dir' => '/', 'filename' => null],
            ['inputPath' => '//', 'expected' => '//', 'dir' => '//', 'filename' => null],

            ['inputPath' => 'z', 'expected' => 'z', 'dir' => '', 'filename' => 'z'],
            ['inputPath' => '/z', 'expected' => '/z', 'dir' => '/', 'filename' => 'z'],
            ['inputPath' => 'z/', 'expected' => 'z/', 'dir' => 'z/', 'filename' => null],
            ['inputPath' => '/z/', 'expected' => '/z/', 'dir' => '/z/', 'filename' => null],

            ['inputPath' => '/', 'expected' => '/', 'dir' => '/', 'filename' => null],
//            ['inputPath' => '//', 'expected' => '//', 'dir' => '//', 'filename' => null],
//            ['inputPath' => '//', 'expected' => '//', 'dir' => '//', 'filename' => null],
            ['inputPath' => '///', 'expected' => '///', 'dir' => '///', 'filename' => null],

            ['inputPath' => '/z/z', 'expected' => '/z/z', 'dir' => '/z/', 'filename' => 'z'],
            ['inputPath' => '//z/z', 'expected' => '//z/z', 'dir' => '//z/', 'filename' => 'z'],
            ['inputPath' => '/z/z/', 'expected' => '/z/z/', 'dir' => '/z/z/', 'filename' => null],
            ['inputPath' => '//z/z/', 'expected' => '//z/z/', 'dir' => '//z/z/', 'filename' => null],

            ['inputPath' => 'z/z/', 'expected' => 'z/z/', 'dir' => 'z/z/', 'filename' => null],
            ['inputPath' => '/z/z/', 'expected' => '/z/z/', 'dir' => '/z/z/', 'filename' => null],
            ['inputPath' => 'z/z//', 'expected' => 'z/z//', 'dir' => 'z/z//', 'filename' => null],
            ['inputPath' => '/z/z//', 'expected' => '/z/z//', 'dir' => '/z/z//', 'filename' => null],
        ];

        $preProcessInput = function (array $params, string $inputSeparator, string $osSeparator) {

            /** @var array{inputPath: string, expected: string, dir: string, filename: string|null} $params */

            $path = str_replace('/', $inputSeparator, $params['inputPath']);
            $expected = str_replace('/', $osSeparator, $params['expected']);
            $dir = str_replace('/', $osSeparator, $params['dir']);
            $filename = $params['filename'];

            return ['inputPath' => $path, 'expected' => $expected, 'dir' => $dir, 'filename' => $filename];
        };

        $return2 = [];
        foreach ($return as $params) {
            $return2[] = $preProcessInput($params, '/', DIRECTORY_SEPARATOR); // linux input
            $return2[] = $preProcessInput($params, '\\', DIRECTORY_SEPARATOR); // windows input
        }

        return $return2;
    }

    /**
     * Test the ::new() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_new(): void
    {
        $path = Path::new('')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::new('/')->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::new('z')->separator('/');
        self::assertSame('z', (string) $path);

        $path = Path::new('/z')->separator('/');
        self::assertSame('/z', (string) $path);

        $path = Path::new('z/')->separator('/');
        self::assertSame('z/', (string) $path);

        $path = Path::new('/z/')->separator('/');
        self::assertSame('/z/', (string) $path);

        $path = Path::new('//z//')->separator('/');
        self::assertSame('//z//', (string) $path);

        // $blockBreakout = not passed
        $path = Path::new('..')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::new('../')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::new('/../')->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::new('a/../')->separator('/');
        self::assertSame('a/../', (string) $path);

        $path = Path::new('/a/../')->separator('/');
        self::assertSame('/a/../', (string) $path);

        $path = Path::new('../b')->separator('/');
        self::assertSame('b', (string) $path);

        $path = Path::new('/../b')->separator('/');
        self::assertSame('/b', (string) $path);

        $path = Path::new('a/../b')->separator('/');
        self::assertSame('a/../b', (string) $path);

        $path = Path::new('/a/../b')->separator('/');
        self::assertSame('/a/../b', (string) $path);

        $path = Path::new('./a/')->separator('/');
        self::assertSame('./a/', (string) $path);

        $path = Path::new('/a/.')->separator('/');
        self::assertSame('/a/.', (string) $path);

        $path = Path::new('/a/./b')->separator('/');
        self::assertSame('/a/./b', (string) $path);

        $path = Path::new('./..')->separator('/');
        self::assertSame('./', (string) $path);

        $path = Path::new('../.')->separator('/');
        self::assertSame('.', (string) $path);

        // $blockBreakout = true
        $path = Path::new('..', true)->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::new('../', true)->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::new('/../', true)->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::new('a/../', true)->separator('/');
        self::assertSame('a/../', (string) $path);

        $path = Path::new('/a/../', true)->separator('/');
        self::assertSame('/a/../', (string) $path);

        $path = Path::new('../b', true)->separator('/');
        self::assertSame('b', (string) $path);

        $path = Path::new('/../b', true)->separator('/');
        self::assertSame('/b', (string) $path);

        $path = Path::new('a/../b', true)->separator('/');
        self::assertSame('a/../b', (string) $path);

        $path = Path::new('/a/../b', true)->separator('/');
        self::assertSame('/a/../b', (string) $path);

        $path = Path::new('./a/', true)->separator('/');
        self::assertSame('./a/', (string) $path);

        $path = Path::new('/a/.', true)->separator('/');
        self::assertSame('/a/.', (string) $path);

        $path = Path::new('/a/./b', true)->separator('/');
        self::assertSame('/a/./b', (string) $path);

        $path = Path::new('./..', true)->separator('/');
        self::assertSame('./', (string) $path);

        $path = Path::new('../.', true)->separator('/');
        self::assertSame('.', (string) $path);

        // $blockBreakout = false
        $path = Path::new('..', false)->separator('/');
        self::assertSame('..', (string) $path);

        $path = Path::new('../', false)->separator('/');
        self::assertSame('../', (string) $path);

        $path = Path::new('/../', false)->separator('/');
        self::assertSame('/../', (string) $path);

        $path = Path::new('a/../', false)->separator('/');
        self::assertSame('a/../', (string) $path);

        $path = Path::new('/a/../', false)->separator('/');
        self::assertSame('/a/../', (string) $path);

        $path = Path::new('../b', false)->separator('/');
        self::assertSame('../b', (string) $path);

        $path = Path::new('/../b', false)->separator('/');
        self::assertSame('/../b', (string) $path);

        $path = Path::new('a/../b', false)->separator('/');
        self::assertSame('a/../b', (string) $path);

        $path = Path::new('/a/../b', false)->separator('/');
        self::assertSame('/a/../b', (string) $path);

        $path = Path::new('./a/', false)->separator('/');
        self::assertSame('./a/', (string) $path);

        $path = Path::new('/a/.', false)->separator('/');
        self::assertSame('/a/.', (string) $path);

        $path = Path::new('/a/./b', false)->separator('/');
        self::assertSame('/a/./b', (string) $path);

        $path = Path::new('./..', false)->separator('/');
        self::assertSame('./..', (string) $path);

        $path = Path::new('../.', false)->separator('/');
        self::assertSame('../.', (string) $path);

        // a couple of PathImmutable tests to test breakout
        $path = PathImmutable::new('../b')->separator('/');
        self::assertSame('b', (string) $path);

        $path = PathImmutable::new('../b', true)->separator('/');
        self::assertSame('b', (string) $path);

        $path = PathImmutable::new('../b', false)->separator('/');
        self::assertSame('../b', (string) $path);
    }

    /**
     * Test the ::newDir() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_new_dir(): void
    {
        $path = Path::newDir('')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newDir('/')->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::newDir('z')->separator('/');
        self::assertSame('z/', (string) $path);

        $path = Path::newDir('/z')->separator('/');
        self::assertSame('/z/', (string) $path);

        $path = Path::newDir('z/')->separator('/');
        self::assertSame('z/', (string) $path);

        $path = Path::newDir('/z/')->separator('/');
        self::assertSame('/z/', (string) $path);

        // $blockBreakout = not passed
        $path = Path::newDir('..')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newDir('../')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newDir('/../')->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::newDir('a/../')->separator('/');
        self::assertSame('a/../', (string) $path);

        $path = Path::newDir('/a/../')->separator('/');
        self::assertSame('/a/../', (string) $path);

        $path = Path::newDir('../b')->separator('/');
        self::assertSame('b/', (string) $path);

        $path = Path::newDir('/../b')->separator('/');
        self::assertSame('/b/', (string) $path);

        $path = Path::newDir('a/../b')->separator('/');
        self::assertSame('a/../b/', (string) $path);

        $path = Path::newDir('/a/../b')->separator('/');
        self::assertSame('/a/../b/', (string) $path);

        $path = Path::newDir('./a/')->separator('/');
        self::assertSame('./a/', (string) $path);

        $path = Path::newDir('/a/.')->separator('/');
        self::assertSame('/a/./', (string) $path);

        $path = Path::newDir('/a/./b')->separator('/');
        self::assertSame('/a/./b/', (string) $path);

        $path = Path::newDir('./..')->separator('/');
        self::assertSame('./', (string) $path);

        $path = Path::newDir('../.')->separator('/');
        self::assertSame('./', (string) $path);

        // $blockBreakout = true
        $path = Path::newDir('..', true)->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newDir('../', true)->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newDir('/../', true)->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::newDir('a/../', true)->separator('/');
        self::assertSame('a/../', (string) $path);

        $path = Path::newDir('/a/../', true)->separator('/');
        self::assertSame('/a/../', (string) $path);

        $path = Path::newDir('../b', true)->separator('/');
        self::assertSame('b/', (string) $path);

        $path = Path::newDir('/../b', true)->separator('/');
        self::assertSame('/b/', (string) $path);

        $path = Path::newDir('a/../b', true)->separator('/');
        self::assertSame('a/../b/', (string) $path);

        $path = Path::newDir('/a/../b', true)->separator('/');
        self::assertSame('/a/../b/', (string) $path);

        $path = Path::newDir('./a/', true)->separator('/');
        self::assertSame('./a/', (string) $path);

        $path = Path::newDir('/a/.', true)->separator('/');
        self::assertSame('/a/./', (string) $path);

        $path = Path::newDir('/a/./b', true)->separator('/');
        self::assertSame('/a/./b/', (string) $path);

        $path = Path::newDir('./..', true)->separator('/');
        self::assertSame('./', (string) $path);

        $path = Path::newDir('../.', true)->separator('/');
        self::assertSame('./', (string) $path);

        // $blockBreakout = false
        $path = Path::newDir('..', false)->separator('/');
        self::assertSame('../', (string) $path);

        $path = Path::newDir('../', false)->separator('/');
        self::assertSame('../', (string) $path);

        $path = Path::newDir('/../', false)->separator('/');
        self::assertSame('/../', (string) $path);

        $path = Path::newDir('a/../', false)->separator('/');
        self::assertSame('a/../', (string) $path);

        $path = Path::newDir('/a/../', false)->separator('/');
        self::assertSame('/a/../', (string) $path);

        $path = Path::newDir('../b', false)->separator('/');
        self::assertSame('../b/', (string) $path);

        $path = Path::newDir('/../b', false)->separator('/');
        self::assertSame('/../b/', (string) $path);

        $path = Path::newDir('a/../b', false)->separator('/');
        self::assertSame('a/../b/', (string) $path);

        $path = Path::newDir('/a/../b', false)->separator('/');
        self::assertSame('/a/../b/', (string) $path);

        $path = Path::newDir('./a/', false)->separator('/');
        self::assertSame('./a/', (string) $path);

        $path = Path::newDir('/a/.', false)->separator('/');
        self::assertSame('/a/./', (string) $path);

        $path = Path::newDir('/a/./b', false)->separator('/');
        self::assertSame('/a/./b/', (string) $path);

        $path = Path::newDir('./..', false)->separator('/');
        self::assertSame('./../', (string) $path);

        $path = Path::newDir('../.', false)->separator('/');
        self::assertSame('.././', (string) $path);
    }

    /**
     * Test the ::newFile() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_new_file(): void
    {
        $path = Path::newFile('')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newFile('/')->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::newFile('//')->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::newFile('z')->separator('/');
        self::assertSame('z', (string) $path);

        $path = Path::newFile('/z')->separator('/');
        self::assertSame('/z', (string) $path);

        $path = Path::newFile('z/')->separator('/');
        self::assertSame('z', (string) $path);

        $path = Path::newFile('z//')->separator('/');
        self::assertSame('z', (string) $path);

        $path = Path::newFile('/z/')->separator('/');
        self::assertSame('/z', (string) $path);

        $path = Path::newFile('/z//')->separator('/');
        self::assertSame('/z', (string) $path);

        // $blockBreakout = not passed
        $path = Path::newFile('..')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newFile('../')->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newFile('/../')->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::newFile('a/../')->separator('/');
        self::assertSame('a/..', (string) $path);

        $path = Path::newFile('/a/../')->separator('/');
        self::assertSame('/a/..', (string) $path);

        $path = Path::newFile('../b')->separator('/');
        self::assertSame('b', (string) $path);

        $path = Path::newFile('/../b')->separator('/');
        self::assertSame('/b', (string) $path);

        $path = Path::newFile('a/../b')->separator('/');
        self::assertSame('a/../b', (string) $path);

        $path = Path::newFile('/a/../b')->separator('/');
        self::assertSame('/a/../b', (string) $path);

        $path = Path::newFile('./a/')->separator('/');
        self::assertSame('./a', (string) $path);

        $path = Path::newFile('/a/.')->separator('/');
        self::assertSame('/a/.', (string) $path);

        $path = Path::newFile('/a/./b')->separator('/');
        self::assertSame('/a/./b', (string) $path);

        $path = Path::newFile('./..')->separator('/');
        self::assertSame('.', (string) $path);

        $path = Path::newFile('../.')->separator('/');
        self::assertSame('.', (string) $path);

        // $blockBreakout = true
        $path = Path::newFile('..', true)->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newFile('../', true)->separator('/');
        self::assertSame('', (string) $path);

        $path = Path::newFile('/../', true)->separator('/');
        self::assertSame('/', (string) $path);

        $path = Path::newFile('a/../', true)->separator('/');
        self::assertSame('a/..', (string) $path);

        $path = Path::newFile('/a/../', true)->separator('/');
        self::assertSame('/a/..', (string) $path);

        $path = Path::newFile('../b', true)->separator('/');
        self::assertSame('b', (string) $path);

        $path = Path::newFile('/../b', true)->separator('/');
        self::assertSame('/b', (string) $path);

        $path = Path::newFile('a/../b', true)->separator('/');
        self::assertSame('a/../b', (string) $path);

        $path = Path::newFile('/a/../b', true)->separator('/');
        self::assertSame('/a/../b', (string) $path);

        $path = Path::newFile('./a/', true)->separator('/');
        self::assertSame('./a', (string) $path);

        $path = Path::newFile('/a/.', true)->separator('/');
        self::assertSame('/a/.', (string) $path);

        $path = Path::newFile('/a/./b', true)->separator('/');
        self::assertSame('/a/./b', (string) $path);

        $path = Path::newFile('./..', true)->separator('/');
        self::assertSame('.', (string) $path);

        $path = Path::newFile('../.', true)->separator('/');
        self::assertSame('.', (string) $path);

        // $blockBreakout = false
        $path = Path::newFile('..', false)->separator('/');
        self::assertSame('..', (string) $path);

        $path = Path::newFile('../', false)->separator('/');
        self::assertSame('..', (string) $path);

        $path = Path::newFile('/../', false)->separator('/');
        self::assertSame('/..', (string) $path);

        $path = Path::newFile('a/../', false)->separator('/');
        self::assertSame('a/..', (string) $path);

        $path = Path::newFile('/a/../', false)->separator('/');
        self::assertSame('/a/..', (string) $path);

        $path = Path::newFile('../b', false)->separator('/');
        self::assertSame('../b', (string) $path);

        $path = Path::newFile('/../b', false)->separator('/');
        self::assertSame('/../b', (string) $path);

        $path = Path::newFile('a/../b', false)->separator('/');
        self::assertSame('a/../b', (string) $path);

        $path = Path::newFile('/a/../b', false)->separator('/');
        self::assertSame('/a/../b', (string) $path);

        $path = Path::newFile('./a/', false)->separator('/');
        self::assertSame('./a', (string) $path);

        $path = Path::newFile('/a/.', false)->separator('/');
        self::assertSame('/a/.', (string) $path);

        $path = Path::newFile('/a/./b', false)->separator('/');
        self::assertSame('/a/./b', (string) $path);

        $path = Path::newFile('./..', false)->separator('/');
        self::assertSame('./..', (string) $path);

        $path = Path::newFile('../.', false)->separator('/');
        self::assertSame('../.', (string) $path);
    }

    /**
     * Test the ->resolve() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_resolve(): void
    {
        $path = Path::new('')->separator('/')->resolve();
        self::assertSame('', (string) $path);

        $path = Path::new('/')->separator('/')->resolve();
        self::assertSame('/', (string) $path);

        $path = Path::new('z')->separator('/')->resolve();
        self::assertSame('z', (string) $path);

        // doesn't start with /
        $path = Path::new('..')->separator('/')->resolve();
        self::assertSame('', (string) $path);
        $path = Path::new('../')->separator('/')->resolve();
        self::assertSame('', (string) $path);

        $path = Path::new('../..')->separator('/')->resolve();
        self::assertSame('', (string) $path);
        $path = Path::new('../../')->separator('/')->resolve();
        self::assertSame('', (string) $path);

        $path = Path::new('a/..')->separator('/')->resolve();
        self::assertSame('', (string) $path);
        $path = Path::new('a/../')->separator('/')->resolve();
        self::assertSame('', (string) $path);

        $path = Path::new('../a')->separator('/')->resolve();
        self::assertSame('a', (string) $path);
        $path = Path::new('../a/')->separator('/')->resolve();
        self::assertSame('a/', (string) $path);

        $path = Path::new('a/b/..')->separator('/')->resolve();
        self::assertSame('a/', (string) $path);
        $path = Path::new('a/b/../')->separator('/')->resolve();
        self::assertSame('a/', (string) $path);

        $path = Path::new('a/.')->separator('/')->resolve();
        self::assertSame('a/', (string) $path);

        $path = Path::new('a/./b')->separator('/')->resolve();
        self::assertSame('a/b', (string) $path);

        $path = Path::new('./a/')->separator('/')->resolve();
        self::assertSame('a/', (string) $path);

        $path = Path::new('./..')->separator('/')->resolve();
        self::assertSame('', (string) $path);

        $path = Path::new('../.')->separator('/')->resolve();
        self::assertSame('', (string) $path);

        $path = Path::new('a/../b')->separator('/')->resolve();
        self::assertSame('b', (string) $path);

        // starts with /
        $path = Path::new('/..')->separator('/')->resolve();
        self::assertSame('/', (string) $path);
        $path = Path::new('/../')->separator('/')->resolve();
        self::assertSame('/', (string) $path);

        $path = Path::new('/../..')->separator('/')->resolve();
        self::assertSame('/', (string) $path);
        $path = Path::new('/../../')->separator('/')->resolve();
        self::assertSame('/', (string) $path);

        $path = Path::new('/a/..')->separator('/')->resolve();
        self::assertSame('/', (string) $path);
        $path = Path::new('/a/../')->separator('/')->resolve();
        self::assertSame('/', (string) $path);

        $path = Path::new('/../a')->separator('/')->resolve();
        self::assertSame('/a', (string) $path);
        $path = Path::new('/../a/')->separator('/')->resolve();
        self::assertSame('/a/', (string) $path);

        $path = Path::new('/a/b/..')->separator('/')->resolve();
        self::assertSame('/a/', (string) $path);
        $path = Path::new('/a/b/../')->separator('/')->resolve();
        self::assertSame('/a/', (string) $path);

        $path = Path::new('/a/.')->separator('/')->resolve();
        self::assertSame('/a/', (string) $path);

        $path = Path::new('/a/./b')->separator('/')->resolve();
        self::assertSame('/a/b', (string) $path);

        $path = Path::new('/./a/')->separator('/')->resolve();
        self::assertSame('/a/', (string) $path);

        $path = Path::new('/./..')->separator('/')->resolve();
        self::assertSame('/', (string) $path);

        $path = Path::new('/../.')->separator('/')->resolve();
        self::assertSame('/', (string) $path);

        $path = Path::new('/a/../b')->separator('/')->resolve();
        self::assertSame('/b', (string) $path);
    }

    /**
     * Test the ->add() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_add(): void
    {
        $path = Path::new('')->separator('/')->add('/');
        self::assertSame('', (string) $path);

        $path = Path::new('/')->separator('/')->add('');
        self::assertSame('/', (string) $path);

        $path = Path::new('/')->separator('/')->add('/');
        self::assertSame('/', (string) $path);

        $path = Path::new('/')->separator('/')->add('a');
        self::assertSame('/a', (string) $path);

        $path = Path::new('/')->separator('/')->add('a/b/');
        self::assertSame('/a/b/', (string) $path);

        $path = Path::new('/A')->separator('/')->add('a'); // 'A' is considered to be a file
        self::assertSame('/a', (string) $path);
        $path = Path::new('/A/')->separator('/')->add('a');
        self::assertSame('/A/a', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('a'); // 'B' is considered to be a file
        self::assertSame('/A/a', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('a');
        self::assertSame('/A/B/a', (string) $path);

        // $blockBreakout = not passed
        $path = Path::new('/A/B')->separator('/')->add('../'); // 'B' is considered to be a file
        self::assertSame('/A/', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../');
        self::assertSame('/A/B/', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('../a'); // 'B' is considered to be a file
        self::assertSame('/A/a', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../a');
        self::assertSame('/A/B/a', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('a/..'); // 'B' is considered to be a file
        self::assertSame('/A/a/..', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('a/..');
        self::assertSame('/A/B/a/..', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('a/../b'); // 'B' is considered to be a file
        self::assertSame('/A/a/../b', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('a/../b');
        self::assertSame('/A/B/a/../b', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('../../a'); // 'B' is considered to be a file
        self::assertSame('/A/a', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../../a');
        self::assertSame('/A/B/a', (string) $path);

        // $blockBreakout = true
        $path = Path::new('/A/B')->separator('/')->add('../', true); // 'B' is considered to be a file
        self::assertSame('/A/', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../', true);
        self::assertSame('/A/B/', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('../a', true); // 'B' is considered to be a file
        self::assertSame('/A/a', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../a', true);
        self::assertSame('/A/B/a', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('a/..', true); // 'B' is considered to be a file
        self::assertSame('/A/a/..', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('a/..', true);
        self::assertSame('/A/B/a/..', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('a/../b', true); // 'B' is considered to be a file
        self::assertSame('/A/a/../b', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('a/../b', true);
        self::assertSame('/A/B/a/../b', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('../../a', true); // 'B' is considered to be a file
        self::assertSame('/A/a', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../../a', true);
        self::assertSame('/A/B/a', (string) $path);

        // $blockBreakout = false
        $path = Path::new('/A/B')->separator('/')->add('../', false); // 'B' is considered to be a file
        self::assertSame('/A/../', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../', false);
        self::assertSame('/A/B/../', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('../a', false); // 'B' is considered to be a file
        self::assertSame('/A/../a', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../a', false);
        self::assertSame('/A/B/../a', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('a/..', false); // 'B' is considered to be a file
        self::assertSame('/A/a/..', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('a/..', false);
        self::assertSame('/A/B/a/..', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('a/../b', false); // 'B' is considered to be a file
        self::assertSame('/A/a/../b', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('a/../b', false);
        self::assertSame('/A/B/a/../b', (string) $path);

        $path = Path::new('/A/B')->separator('/')->add('../../a', false); // 'B' is considered to be a file
        self::assertSame('/A/../a', (string) $path);
        $path = Path::new('/A/B/')->separator('/')->add('../../a', false);
        self::assertSame('/A/B/../../a', (string) $path);

        // with new() and add() having $blockBreakout = false
        $path = Path::new('/A/', false)->separator('/')->add('../../a', false);
        self::assertSame('/A/../../a', (string) $path);
    }

    /**
     * Test the ->getDir() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_get_dir(): void
    {
        $path = Path::new('')->separator('/');
        self::assertSame('', (string) $path->getDir());

        $path = Path::new('/')->separator('/');
        self::assertSame('/', (string) $path->getDir());



        $path = Path::new('a')->separator('/');
        self::assertSame('', (string) $path->getDir());

        $path = Path::new('a/')->separator('/');
        self::assertSame('a/', (string) $path->getDir());

        $path = Path::new('a/a')->separator('/');
        self::assertSame('a/', (string) $path->getDir());

        $path = Path::new('a/a/')->separator('/');
        self::assertSame('a/a/', (string) $path->getDir());

        $path = Path::new('a/.')->separator('/');
        self::assertSame('a/.', (string) $path->getDir());

        $path = Path::new('a/./')->separator('/');
        self::assertSame('a/./', (string) $path->getDir());

        $path = Path::new('a/..')->separator('/');
        self::assertSame('a/..', (string) $path->getDir());

        $path = Path::new('a/../')->separator('/');
        self::assertSame('a/../', (string) $path->getDir());



        $path = Path::new('/a')->separator('/');
        self::assertSame('/', (string) $path->getDir());

        $path = Path::new('/a/')->separator('/');
        self::assertSame('/a/', (string) $path->getDir());

        $path = Path::new('/a/a')->separator('/');
        self::assertSame('/a/', (string) $path->getDir());

        $path = Path::new('/a/a/')->separator('/');
        self::assertSame('/a/a/', (string) $path->getDir());

        $path = Path::new('/a/.')->separator('/');
        self::assertSame('/a/.', (string) $path->getDir());

        $path = Path::new('/a/./')->separator('/');
        self::assertSame('/a/./', (string) $path->getDir());

        $path = Path::new('/a/..')->separator('/');
        self::assertSame('/a/..', (string) $path->getDir());

        $path = Path::new('/a/../')->separator('/');
        self::assertSame('/a/../', (string) $path->getDir());
    }

    /**
     * Test the ->getFilename() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_get_filename(): void
    {
        $path = Path::new('')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('/')->separator('/');
        self::assertSame(null, $path->getFilename());



        $path = Path::new('a')->separator('/');
        self::assertSame('a', $path->getFilename());

        $path = Path::new('a/')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('a/a')->separator('/');
        self::assertSame('a', $path->getFilename());

        $path = Path::new('a/a/')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('a/.')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('a/./')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('a/..')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('a/../')->separator('/');
        self::assertSame(null, $path->getFilename());



        $path = Path::new('/a')->separator('/');
        self::assertSame('a', $path->getFilename());

        $path = Path::new('/a/')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('/a/a')->separator('/');
        self::assertSame('a', $path->getFilename());

        $path = Path::new('/a/a/')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('/a/.')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('/a/./')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('/a/..')->separator('/');
        self::assertSame(null, $path->getFilename());

        $path = Path::new('/a/../')->separator('/');
        self::assertSame(null, $path->getFilename());



        $path = Path::new('a')->separator('/');
        self::assertSame('a', $path->getFilename());

        $path = Path::new('a')->separator('/');
        self::assertSame('a', $path->getFilename(true));

        $path = Path::new('a')->separator('/');
        self::assertSame('a', $path->getFilename(false));


        $path = Path::new('a.txt')->separator('/');
        self::assertSame('a.txt', $path->getFilename());

        $path = Path::new('a.txt')->separator('/');
        self::assertSame('a.txt', $path->getFilename(true));

        $path = Path::new('a.txt')->separator('/');
        self::assertSame('a', $path->getFilename(false));


        $path = Path::new('a.b.c')->separator('/');
        self::assertSame('a.b.c', $path->getFilename());

        $path = Path::new('a.b.c')->separator('/');
        self::assertSame('a.b.c', $path->getFilename(true));

        $path = Path::new('a.b.c')->separator('/');
        self::assertSame('a.b', $path->getFilename(false));


        $path = Path::new('a.b.c.txt')->separator('/');
        self::assertSame('a.b.c.txt', $path->getFilename());

        $path = Path::new('a.b.c.txt')->separator('/');
        self::assertSame('a.b.c.txt', $path->getFilename(true));

        $path = Path::new('a.b.c.txt')->separator('/');
        self::assertSame('a.b.c', $path->getFilename(false));
    }

    /**
     * Test the ->getFilename() method.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_get_extension(): void
    {
        $path = Path::new('')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('a')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('a.txt')->separator('/');
        self::assertSame('.txt', $path->getExtension());


        $path = Path::new('/')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('/a')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('/a.txt')->separator('/');
        self::assertSame('.txt', $path->getExtension());


        $path = Path::new('/a/')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('/a/')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('/a/.txt')->separator('/');
        self::assertSame('.txt', $path->getExtension());


        $path = Path::new('/a/b')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('/a/b')->separator('/');
        self::assertSame(null, $path->getExtension());

        $path = Path::new('/a/b.txt')->separator('/');
        self::assertSame('.txt', $path->getExtension());

        $path = Path::new('a.b.c.txt')->separator('/');
        self::assertSame('.txt', $path->getExtension());


        $path = Path::new('a.b.c.txt')->separator('/');
        self::assertSame('.txt', $path->getExtension(true));
        self::assertSame('txt', $path->getExtension(false));
    }

    /**
     * Test the ->isAbsolute() and isRelative() methods.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_is_absolute_and_is_relative(): void
    {
        $path = Path::new('')->separator('/');
        self::assertFalse($path->isAbsolute());
        self::assertTrue($path->isRelative());

        $path = Path::new('abc')->separator('/');
        self::assertFalse($path->isAbsolute());
        self::assertTrue($path->isRelative());

        $path = Path::new('../abc')->separator('/');
        self::assertFalse($path->isAbsolute());
        self::assertTrue($path->isRelative());

        $path = Path::new('/')->separator('/');
        self::assertTrue($path->isAbsolute());
        self::assertFalse($path->isRelative());

        $path = Path::new('/abc')->separator('/');
        self::assertTrue($path->isAbsolute());
        self::assertFalse($path->isRelative());


        $path = Path::new('abc')->separator('\\');
        self::assertFalse($path->isAbsolute());
        self::assertTrue($path->isRelative());

        $path = Path::new('/abc')->separator('\\');
        self::assertTrue($path->isAbsolute());
        self::assertFalse($path->isRelative());


        $path = Path::new('abc');
        self::assertFalse($path->isAbsolute());
        self::assertTrue($path->isRelative());

        $path = Path::new('\\abc');
        self::assertTrue($path->isAbsolute());
        self::assertFalse($path->isRelative());
    }



    /**
     * Test that DIRECTORY_SEPARATOR is used as a separator when generating output, if none was explicitly set.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_separator_when_not_specified(): void
    {
        $input = '/a/b/c/';
        $expected = str_replace('/', DIRECTORY_SEPARATOR, $input);

        $path = Path::new($input);
        self::assertSame($expected, (string) $path);
    }

    /**
     * Test that Path accepts both '\' and '/' directory separators in input, and renders them as expected based on the
     * ->separator() value specified by the caller.
     *
     * @test
     * @dataProvider separatorDataProvider
     *
     * @param string      $inputPath The path to use as input.
     * @param string|null $separator The separator to use.
     * @param string      $expected  The expected result.
     * @return void
     */
    #[Test]
    #[DataProvider('separatorDataProvider')]
    public function test_separator(string $inputPath, ?string $separator, string $expected): void
    {
        $path = Path::new($inputPath)->separator($separator);
        self::assertSame($expected, (string) $path);
    }

    /**
     * Data provider for test_separator().
     *
     * @return array<array<string, string|null>>
     */
    public static function separatorDataProvider(): array
    {
        $sep = DIRECTORY_SEPARATOR;

        return [
            // input with '/'
            [
                'inputPath' => '/a/b/c/',
                'separator' => null,
                'expected' => "{$sep}a{$sep}b{$sep}c{$sep}",
            ],
            [
                'inputPath' => '/a/b/c/',
                'separator' => '/',
                'expected' => "/a/b/c/",
            ],
            [
                'inputPath' => '/a/b/c/',
                'separator' => '\\',
                'expected' => "\\a\\b\\c\\",
            ],

            // input with '\'
            [
                'inputPath' => '\\a\\b\\c\\',
                'separator' => null,
                'expected' => "{$sep}a{$sep}b{$sep}c{$sep}",
            ],
            [
                'inputPath' => '\\a\\b\\c\\',
                'separator' => '/',
                'expected' => "/a/b/c/",
            ],
            [
                'inputPath' => '\\a\\b\\c\\',
                'separator' => '\\',
                'expected' => "\\a\\b\\c\\",
            ],
        ];
    }



    /**
     * Test the immutability-ness of Path and PathImmutable classes.
     *
     * @test
     *
     * @return void
     */
    #[Test]
    public function test_immutability(): void
    {
        // what is Path?
        $path = Path::new('/');
        self::assertInstanceOf(AbstractPath::class, $path);
        self::assertInstanceOf(Path::class, $path);
        self::assertNotInstanceOf(PathImmutable::class, $path);

        // what is PathImmutable?
        $path = PathImmutable::new('/');
        self::assertInstanceOf(AbstractPath::class, $path);
        self::assertNotInstanceOf(Path::class, $path);
        self::assertInstanceOf(PathImmutable::class, $path);



        // ::new(..)
        $path = Path::new('/');
        self::assertInstanceOf(Path::class, $path);
        $path = PathImmutable::new('/');
        self::assertInstanceOf(PathImmutable::class, $path);

        // ::newDir(..)
        $path = Path::newDir('/');
        self::assertInstanceOf(Path::class, $path);
        $path = PathImmutable::newDir('/');
        self::assertInstanceOf(PathImmutable::class, $path);

        // ::newFile(..)
        $path = Path::newFile('/');
        self::assertInstanceOf(Path::class, $path);
        $path = PathImmutable::newFile('/');
        self::assertInstanceOf(PathImmutable::class, $path);



        // ->copy()
        $path = Path::new('/');
        self::assertNotSame($path, $path->copy()); // ->copy() always produces a different instance
        $path = PathImmutable::new('/');
        self::assertNotSame($path, $path->copy());

        // ->resolve()
        $path = Path::new('/');
        self::assertSame($path, $path->resolve());
        $path = PathImmutable::new('/');
        self::assertNotSame($path, $path->resolve());

        // ->add('/')
        $path = Path::new('/');
        self::assertSame($path, $path->add('/'));
        $path = PathImmutable::new('/');
        self::assertNotSame($path, $path->add('/'));

        // ->separator('/')
        $path = Path::new('/');
        self::assertSame($path, $path->separator('/'));
        $path = PathImmutable::new('/');
        self::assertNotSame($path, $path->separator('/'));

        // ->getDir()
        $path = Path::new('/');
        self::assertNotSame($path, $path->getDir()); // is always different
        $path = PathImmutable::new('/');
        self::assertNotSame($path, $path->getDir());

        // ->getFilename()
        $path = Path::new('/file.txt');
        self::assertIsString($path->getFilename()); // returns string or null
        $path = PathImmutable::new('/file.txt');
        self::assertIsString($path->getFilename()); // returns string or null

        // ->getExtension()
        $path = Path::new('/file.txt');
        self::assertIsString($path->getExtension()); // returns string or null
        $path = PathImmutable::new('/file.txt');
        self::assertIsString($path->getExtension()); // returns string or null
    }
}
