<?php

namespace Xi\Bundle\FilelibBundle\Twig\Extension;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Renderer\SymfonyRenderer;
use Xi\Filelib\File\File;
use Symfony\Component\Routing\RouterInterface;

class FilelibExtension extends \Twig_Extension
{
    /**
     *
     * @var FileLibrary;
     */
    protected $filelib;
    
    /**
     *
     * @var Renderer
     */
    protected $renderer;
    
    /**
     * @var RouterInterface
     */
    protected $router;
    
    public function __construct(FileLibrary $filelib, SymfonyRenderer $renderer, RouterInterface $router)
    {
        $this->filelib = $filelib;
        $this->renderer = $renderer;
        $this->router = $router;
    }

    public function getFunctions()
    {
        return array(
            'filelib_file' => new \Twig_Function_Method($this, 'getFile', array('is_safe' => array('html'))),
            'filelib_url' => new \Twig_Function_Method($this, 'getFileUrl', array('is_safe' => array('html'))),
            'filelib_render' => new \Twig_Function_Method($this, 'getRenderUrl', array('is_safe' => array('html'))),
        );
    }
    

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'filelib';
    }
    
    /**
     * Asserts that file is valid
     * 
     * @param mixed $file
     * @return File
     */
    private function assertFileIsValid($file)
    {
        if (is_numeric($file)) {
            $file = $this->filelib->getFileOperator()->find($file);
        }
        
        if (!$file instanceof File) {
            throw new \InvalidArgumentException('Invalid file');
        }
        
        return $file;

    }

    
    public function getFile($file, $version = 'original')
    {
        $file = $this->assertFileIsValid($file);
        
        if ($this->filelib->getAcl()->isFileReadableByAnonymous($file)) {
            return $this->getFileUrl($file, $version);
        }
        
        return $this->getRenderUrl($file, $version);
        
    }

    
    
    public function getFileUrl($file, $version = 'original')
    {
        $file = $this->assertFileIsValid($file);
        return $this->renderer->getUrl($file, array('version' => $version));
    }
    

    public function getRenderUrl($file, $version = 'original')
    {
        $file = $this->assertFileIsValid($file);
        $url = $this->router->generate('xi_filelib_render', array('id' => $file->getId(), 'version' => $version));
        return $url;
    }
    
}
