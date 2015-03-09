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
        return is_string($original) && (substr($original, 0, 5) === 'data:');
	}


    /**
     * {@inheritdoc}
     */
    public static function save(Uploader $uploader, $original)
    {
        if (!$uploader->getFilename()) {
            $uploader->setFilename(uniqid());
        }

        $fileData = explode(';base64,', $original, 2);

        if (!$uploader->getExtension() && preg_match('|data:\w+/(\w+)|', $fileData[0], $match)) {
            $uploader->setExtension($match[1]);
        }

        $destination = $uploader->getDestination();

        if (!@file_put_contents($destination, base64_decode($fileData[1]))) {
            throw new \Exception("Unable to copy base64 to '{$destination}'");
        }
    }
}