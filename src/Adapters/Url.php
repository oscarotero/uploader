<?php
namespace Uploader\Adapters;

use Uploader\Uploader;

/**
 * Adapter to save a file from an url
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
    public static function save(Uploader $uploader, $original)
    {
        $path = Uploader::parsePath(parse_url($original, PHP_URL_PATH));

        if (!$uploader->getFilename()) {
            $uploader->setFilename($uploaded['name']);
        }

        if (!$uploader->getExtension()) {
            $uploader->setExtension($uploaded['extension']);
        }

        $destination = $uploader->getDestination();

        if (!@rename($original['tmp_name'], $destination)) {
            throw new \Exception("Unable to copy '{$original['tmp_name']}' to '{$destination}'");
        }
    }
}