<?php

namespace Xi\Bundle\FilelibBundle\Twig\Extension;

use Xi\Filelib\FileLibrary;
use Xi\Filelib\Renderer\SymfonyRenderer;
use Xi\Filelib\File\FileItem;

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
    
    
    public function __construct(FileLibrary $filelib, SymfonyRenderer $renderer)
    {
        $this->filelib = $filelib;
        $this->renderer = $renderer;
    }

    public function getFunctions()
    {
        return array(
            'filelib_url' => new \Twig_Function_Method($this, 'getFileUrl', array('is_safe' => array('html'))),
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
    
    
    public function getFileUrl($file, $version = 'default')
    {
        if (is_numeric($file)) {
            $file = $this->filelib->getFileOperator()->find($file);
        }
        
        if (!$file instanceof FileItem) {
            throw new \InvalidArgumentException('Invalid file');
        }        
        
        return $this->renderer->getUrl($file, array('version' => $version));
    }
    
    
}
