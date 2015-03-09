<?php
namespace Uploader\Adapters;

use Uploader\Uploader;

/**
 * Interface used by all adapters
 */
interface AdapterInterface
{
	/**
	 * Check whether or not the value has the right format
	 * 
	 * @param mixed $original
	 * 
	 * @return boolean
	 */
	public static function check($original);


    /**
     * Save the file
     *
     * @param Uploader $uploader
     * @param mixed    $original
     *
     * @throws \Exception On error
     *
     * @return string The created filename
     */
    public function save(Uploader $uploader, $original);
}
