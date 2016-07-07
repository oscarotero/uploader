<?php

namespace Uploader\Adapters;

use Uploader\Uploader;

/**
 * Adapter to save a file from an url.
 */
class Url implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function check($original)
    {
        return is_string($original) && filter_var($original, FILTER_VALIDATE_URL);
    }

    /**
     * {@inheritdoc}
     */
    public static function fixDestination(Uploader $uploader, $original)
    {
        $path = Uploader::parsePath(parse_url($original, PHP_URL_PATH));

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
        if (!@rename($original['tmp_name'], $destination)) {
            throw new \RuntimeException("Unable to copy '{$original['tmp_name']}' to '{$destination}'");
        }
    }
}
