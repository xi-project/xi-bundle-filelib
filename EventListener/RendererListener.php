<?php

namespace Xi\Bundle\FilelibBundle\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Xi\Filelib\Renderer\SymfonyRenderer;

/**
 * Listens to request and injects renderer with request context
 *
 * @author pekkis
 */
class RendererListener
{
    private $renderer;
    
    public function __construct(SymfonyRenderer $renderer)
    {
        $this->renderer = $renderer;
    }
    
    

    public function onKernelRequest(GetResponseEvent $event)
    {
        // React to master requests only
        if ($event->getRequestType() !== HttpKernelInterface::MASTER_REQUEST) {
            return;
        }
        
        $request = $event->getRequest();

        
        
        
    }
    
}
