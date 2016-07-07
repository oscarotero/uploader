<?php

namespace Uploader\Adapters;

use Uploader\Uploader;

/**
 * Adapter to save a file from an url.
 */
class Base64 implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function check($original)
    {
        return is_string($original) && (substr($original, 0, 5) === 'data:');
    }

    /**
     * {@inheritdoc}
     */
    public static function fixDestination(Uploader $uploader, $original)
    {
        if (!$uploader->getFilename()) {
            $uploader->setFilename(uniqid());
        }

        $fileData = explode(';base64,', $original, 2);

        if (!$uploader->getExtension() && preg_match('|data:\w+/(\w+)|', $fileData[0], $match)) {
            $uploader->setExtension($match[1]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function save($original, $destination)
    {
        $fileData = explode(';base64,', $original, 2);

        if (!@file_put_contents($destination, base64_decode($fileData[1]))) {
            throw new \RuntimeException("Unable to copy base64 to '{$destination}'");
        }
    }
}
