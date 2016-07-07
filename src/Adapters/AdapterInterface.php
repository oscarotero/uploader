<?php

namespace Uploader\Adapters;

use Uploader\Uploader;

/**
 * Interface used by all adapters.
 */
interface AdapterInterface
{
    /**
     * Check whether or not the value has the right format.
     *
     * @param mixed $original
     *
     * @return bool
     */
    public static function check($original);

    /**
     * Set the right destination according with the original source.
     *
     * @param Uploader $uploader
     * @param mixed    $original
     */
    public static function fixDestination(Uploader $uploader, $original);

    /**
     * Save the file.
     *
     * @param mixed  $original
     * @param string $destination
     *
     * @throws \RuntimeException On error
     */
    public static function save($original, $destination);
}
