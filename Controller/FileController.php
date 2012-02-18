<?php

namespace Xi\Bundle\FilelibBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class FileController extends Controller
{
    
    
    public function renderAction($id, $version = 'original', $download = false)
    {
        $fl = $this->get('filelib');
        $renderer = $this->get('filelib.renderer');
        
        $file = $fl->file()->find($id);
        if(!$file) {
            throw $this->createNotFoundException();
        }

        return $renderer->render($file, array(
            'version' => $version,
            'download' => $download,
        ));
    }
    
    
    public function renderByFilenameAction($fileName, $version = 'original', $download = false)
    {
        $fl = $this->get('filelib');
        
        $file = $fl->file()->find(36);
        if(!$file) {
            return $this->createNotFoundException();
            // throw new Emerald_Common_Exception('File not found', 404);
        }
        $opts = array();

        if ($version && $version != 'original') {
            $opts['version'] = $version;
        }
        
        if ($download) {
            $opts['download'] = true;
        }

        die('xooxer');
        
        
        
        // When readable by anonymous, redirect to pretty url
        if ($fl->file()->isReadableByAnonymous($file)) {
            $url = $fl->file()->getUrl($file, $opts);
            return $this->redirect($url, 302);
        }
        
        // Convert all exceptions to 404's
        try {
            
            $response = new Response();
            
            
            if (isset($opts['download'])) {
                $response->headers->set('Content-disposition', "attachment; filename={$file->getName()}");
            }

            $response->headers->set('Content-Type', $file->getMimetype());
            
            $response->setContent($fl->file()->render($file, $opts));
            
            return $response;
            
        } catch (\Exception $e) {
           return $this->createNotFoundException();
        }

        
        
        
        
    }
    
    
    
    
    
}

