<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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

