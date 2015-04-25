<?php
namespace Uploader\Adapters;

use Uploader\Uploader;

/**
 * Adapter to save a file from upload ($_FILES)
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
            throw new \Exception("Unable to copy the uploaded file because has an error");
        }

        if (!copy($original['tmp_name'], $destination)) {
            throw new \Exception("Unable to copy '{$original['tmp_name']}' to '{$destination}'");
        }

        chmod($destination, 0755);
    }
}
