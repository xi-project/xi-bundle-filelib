<?php

/**
 * This file is part of the Xi FilelibBundle package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Bundle\FilelibBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Xi\Filelib\Renderer\Renderer;
use Xi\Filelib\FileLibrary;

class FileController extends Controller
{
    public function renderAction($id, $version = 'original', $download = false, $track = false)
    {
        $filelib = $this->getFilelib();
        $renderer = $this->getRenderer();

        $file = $filelib->getFileRepository()->find($id);

        if (!$file) {
            throw $this->createNotFoundException();
        }

        return $renderer->render($file, $version, array(
            'download' => $download,
            'track'    => $track,
        ));
    }

    /**
     * @return Renderer
     */
    protected function getRenderer()
    {
        return $this->get('xi_filelib.renderer');
    }

    /**
     * @return FileLibrary
     */
    protected function getFilelib()
    {
        return $this->get('xi_filelib');
    }
}
