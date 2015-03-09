<?php
namespace Uploader;

abstract class Uploader
{
    protected $cwd;
    protected $adapters = [
        'Uploader\\Adapters\\Base64',
        'Uploader\\Adapters\\Upload',
        'Uploader\\Adapters\\Url',
    ];
    protected $destination = [
        'directory' => null,
        'filename' => null,
        'extension' => null,
    ];

    /**
     * @param string $cwd The current working directory used to save the file
     */
    public function __construct($cwd)
    {
        $this->cwd = $cwd;
    }

    /**
     * Check whether or not the file destination exists
     * 
     * @return boolean
     */
    public function exists()
    {
        return is_file($this->getDestination());
    }

    /**
     * Set the destination of the file. It includes the directory, filename and extension
     * 
     * @param string $destination
     */
    public function setDestination($destination)
    {
        $this->destination = self::parsePath($destination);
    }

    /**
     * Returns the file destination
     * 
     * @param boolean $absolute Whether or not returns the cwd
     * 
     * @return string
     */
    public function getDestination($absolute = true)
    {
        return self::fixPath(($absolute ? '/'.$this->cwd : ''), $this->destination['directory'], $this->destination['filename'].'.'.$this->destination['extension']);
    }

    /**
     * Set only the directory of the destination
     * 
     * @param string $directory
     */
    public function setDirectory($directory)
    {
        $this->destination['directory'] = $directory;
    }

    /**
     * Get the directory of the destination
     * 
     * @return null|string
     */
    public function getDirectory()
    {
        return empty($this->destination['directory']) ? null : $this->destination['directory'];
    }

    /**
     * Set only the filename of the destination
     * 
     * @param string $filename
     */
    public function setFilename($filename)
    {
        $this->destination['filename'] = $filename;
    }

    /**
     * Get the filename of the destination
     * 
     * @return null|string
     */
    public function getFilename()
    {
        return empty($this->destination['filename']) ? null : $this->destination['filename'];
    }

    /**
     * Set only the file extension of the destination
     * 
     * @param string $extension
     */
    public function setExtension($extension)
    {
        $this->destination['extension'] = $extension;
    }

    /**
     * Get the extension of the destination
     * 
     * @return null|string
     */
    public function getExtension()
    {
        return empty($this->destination['extension']) ? null : $this->destination['extension'];
    }

    /**
     * Save the file
     *
     * @param mixed $original
     * @param null|string $adapter
     * 
     * @throws \Exception On error
     *
     * @return string The created filename
     */
    public function save($original, $adapter = null)
    {
        if ($adapter === null) {
            foreach ($this->adapters as $each) {
                if ($each::check($original)) {
                    $adapter = $each;
                    break;
                }
            }
        }

        if ($adapter === null || !class_exists($adapter)) {
            throw new \InvalidArgumentException('No valid adapter found');
        }

        $adapter::save($this, $original);

        return $this->getDestination(false);
    }

    /**
     * Helper function used to parse a path
     * 
     * @param string $path
     * 
     * @return array With 3 keys: directory, filename and extension
     */
    public static function parsePath($path)
    {
        $components = pathinfo($path);

        return [
            'directory' => isset($components['dirname']) ? self::fixPath($components['dirname']) : null,
            'filename' => isset($components['filename']) ? $components['filename'] : null,
            'extension' => isset($components['extension']) ? $components['extension'] : null
        ];
    }

    private static function fixPath($path)
    {
        if (func_num_args() > 1) {
            return static::fixPath(implode('/', func_get_args()));
        }

        $replace = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];

        do {
            $path = preg_replace($replace, '/', $path, -1, $n);
        } while ($n > 0);

        return $path;
    }
}
