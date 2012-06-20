<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\Tests\Twig\Extension;

use PHPUnit_Framework_TestCase;
use Xi\Bundle\FilelibBundle\Twig\Extension\FilelibExtension;
use Xi\Filelib\File\File;
use Twig_Function_Method;

class FilelibExtensionTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var FilelibExtension
     */
    private $filelibExtension;

    private $fileOperator;
    private $file;

    protected function setUp()
    {
        $this->fileOperator = $this->getMock('Xi\Filelib\File\FileOperator');

        $filelib = $this->getMock('Xi\Filelib\FileLibrary');
        $filelib->expects($this->any())
                ->method('getFileOperator')
                ->will($this->returnValue($this->fileOperator));

        $this->file = $this->getMock('Xi\Filelib\File\File');

        $this->filelibExtension = new FilelibExtension(
            $filelib,
            $this->getMockBuilder('Xi\Filelib\Renderer\SymfonyRenderer')
                 ->disableOriginalConstructor()
                 ->getMockForAbstractClass(),
            $this->getMock('Symfony\Component\Routing\RouterInterface')
        );
    }

    /**
     * @test
     */
    public function isFileCompletedReturnsTrueWhenFileStatusIsCompleted()
    {
        $this->file->expects($this->once())
                   ->method('getStatus')
                   ->will($this->returnValue(File::STATUS_COMPLETED));

        $this->assertTrue($this->filelibExtension->isFileCompleted($this->file));
    }

    /**
     * @test
     */
    public function isFileCompletedReturnsFalseWhenFileStatusIsNotCompleted()
    {
        $this->file->expects($this->once())
                   ->method('getStatus')
                   ->will($this->returnValue(File::STATUS_RAW));

        $this->assertFalse($this->filelibExtension->isFileCompleted($this->file));
    }

    /**
     * @test
     */
    public function isFileCompletedGetsFileWhenGivenAFileId()
    {
        $this->file->expects($this->once())
                   ->method('getStatus')
                   ->will($this->returnValue(File::STATUS_COMPLETED));

        $this->fileOperator->expects($this->once())
                           ->method('find')
                           ->with(123)
                           ->will($this->returnValue($this->file));

        $this->assertTrue($this->filelibExtension->isFileCompleted(123));
    }

    /**
     * @test
     */
    public function isFileCompletedThrowsExceptionWhenFileIsNotFound()
    {
        $this->fileOperator->expects($this->once())
                           ->method('find')
                           ->with(123)
                           ->will($this->returnValue(null));

        $this->setExpectedException('InvalidArgumentException', 'Invalid file');

        $this->filelibExtension->isFileCompleted(123);
    }

    /**
     * @test
     */
    public function getsFunctions()
    {
        $this->assertEquals(
            array(
                'filelib_file' => new Twig_Function_Method($this->filelibExtension, 'getFile', array('is_safe' => array('html'))),
                'filelib_url' => new Twig_Function_Method($this->filelibExtension, 'getFileUrl', array('is_safe' => array('html'))),
                'filelib_render' => new Twig_Function_Method($this->filelibExtension, 'getRenderUrl', array('is_safe' => array('html'))),
                'filelib_is_file_completed' => new Twig_Function_Method($this->filelibExtension, 'isFileCompleted'),
            ),
            $this->filelibExtension->getFunctions()
        );
    }
}
