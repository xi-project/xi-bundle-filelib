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

        $file = $fl->getFileOperator()->find($id);
        if (!$file) {
            throw $this->createNotFoundException();
        }

        return $renderer->render($file, array(
            'version' => $version,
            'download' => $download,
        ));
    }

}

