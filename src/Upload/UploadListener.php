<?php

namespace Martial\Warez\Upload;

use Martial\Warez\Upload\Freebox\FreeboxManager;
use Symfony\Component\HttpKernel\Event\PostResponseEvent;

class UploadListener
{
    /**
     * Deletes the generated archives after they are downloaded.
     *
     * @param PostResponseEvent $event
     */
    public function onKernelTerminate(PostResponseEvent $event)
    {
        $request = $event->getRequest();
        $route = $request->attributes->get('_route');

        if ($route != 'upload_file') {
            return;
        }

        $uploadType = $request->query->get('upload-type');

        if ($uploadType == FreeboxManager::UPLOAD_TYPE_ARCHIVE) {
            $file = $request->query->get('filename');
            unlink($file);
        }
    }
}
