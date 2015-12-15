<?php

namespace Uploader\Adapters;

use Uploader\Uploader;
use Psr\Http\Message\UploadedFileInterface;

/**
 * Adapter to save a file from Psr7's UploadedFileInterface.
 */
class Psr7 implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function check($original)
    {
        return ($original instanceof UploadedFileInterface);
    }

    /**
     * {@inheritdoc}
     */
    public static function fixDestination(Uploader $uploader, $original)
    {
        $path = Uploader::parsePath($original->getClientFilename());

        if (!$uploader->getFilename()) {
            $uploader->setFilename($path['filename']);
        }

        if (!$uploader->getExtension()) {
            $uploader->setExtension($path['extension']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function save($original, $destination)
    {
        $original->moveTo($destination);
    }
}
