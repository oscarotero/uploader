<?php

namespace Uploader\Adapters;

use Uploader\Uploader;

/**
 * Adapter to save a file from upload ($_FILES).
 */
class Upload implements AdapterInterface
{
    /**
     * {@inheritdoc}
     */
    public static function check($original)
    {
        return is_array($original) && isset($original['tmp_name']);
    }

    /**
     * {@inheritdoc}
     */
    public static function fixDestination(Uploader $uploader, $original)
    {
        $path = Uploader::parsePath($original['name']);

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
        if (empty($original['tmp_name']) || !empty($original['error'])) {
            throw new \RuntimeException('Unable to copy the uploaded file because has an error');
        }

        $moved = php_sapi_name() == 'cli' ? rename($original['tmp_name'], $destination) : move_uploaded_file($original['tmp_name'], $destination);

        if (!$moved) {
            throw new \RuntimeException("Unable to copy '{$original['tmp_name']}' to '{$destination}'");
        }

        chmod($destination, 0755);
    }
}
