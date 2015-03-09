<?php
namespace Uploader;

class Uploader
{
    protected $cwd;
    protected $prefix;
    protected $overwrite = false;

    protected $adapters = [
        'Uploader\\Adapters\\Base64',
        'Uploader\\Adapters\\Upload',
        'Uploader\\Adapters\\Url',
    ];

    protected $adapter;
    protected $original;
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
     * Set a prefix for the filenames
     * 
     * @param string $prefix
     * 
     * @return $this
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;

        return $this;
    }

    /**
     * Set the overwrite configuration
     * 
     * @param boolean $overwrite
     * 
     * @return $this
     */
    public function setOverwrite($overwrite)
    {
        $this->overwrite = (boolean) $overwrite;

        return $this;
    }

    /**
     * Set the destination of the file. It includes the directory, filename and extension
     * 
     * @param string $destination
     * 
     * @return $this
     */
    public function setDestination($destination)
    {
        $this->destination = self::parsePath($destination);

        return $this;
    }

    /**
     * Returns the file destination
     * 
     * @param boolean $absolute Whether or not returns the cwd
     * 
     * @return string
     */
    public function getDestination($absolute = false)
    {
        return self::fixPath(($absolute ? '/'.$this->cwd : ''), $this->getDirectory(), $this->getFilename().'.'.$this->getExtension());
    }

    /**
     * Set only the directory of the destination
     * 
     * @param string $directory
     * 
     * @return $this
     */
    public function setDirectory($directory)
    {
        $this->destination['directory'] = $directory;

        return $this;
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
     * 
     * @return $this
     */
    public function setFilename($filename)
    {
        $this->destination['filename'] = $filename;

        return $this;
    }

    /**
     * Get the filename of the destination
     * 
     * @return null|string
     */
    public function getFilename()
    {
        return empty($this->destination['filename']) ? null : $this->prefix.$this->destination['filename'];
    }

    /**
     * Set only the file extension of the destination
     * 
     * @param string $extension
     * 
     * @return $this
     */
    public function setExtension($extension)
    {
        $this->destination['extension'] = $extension;

        return $this;
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
     * Set the original source
     *
     * @param mixed $original
     * @param null|string $adapter
     * 
     * @throws \Exception On error
     *
     * @return Uploader A new cloned copy with the source and adapter configured
     */
    public function with($original, $adapter = null)
    {
        $new = clone $this;
        $new->original = $original;
        $new->adapter = $adapter;

        if ($new->adapter === null) {
            foreach ($new->adapters as $each) {
                if ($each::check($original)) {
                    $new->adapter = $each;
                    break;
                }
            }
        }

        if ($new->adapter === null || !class_exists($new->adapter)) {
            throw new \InvalidArgumentException('No valid adapter found');
        }

        return $new;
    }



    /**
     * Save the file
     *
     * @throws \Exception On error
     *
     * @return $this
     */
    public function save()
    {
        if (!$this->original || !$this->adapter) {
            throw new \Exception('Original source is not defined');
        }

        call_user_func("{$this->adapter}::fixDestination", $this, $this->original);

        $destination = $this->getDestination(true);

        if ($this->overwrite || !is_file($destination)) {
            call_user_func("{$this->adapter}::save", $this->original, $destination);
        }

        return $this;
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

    /**
     * Resolve paths with ../, //, etc...
     * 
     * @param string $path
     * 
     * @return string
     */
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
