<?php

namespace Uploader;

use Closure;

class Uploader
{
    protected $cwd;
    protected $adapter;
    protected $original;

    protected $adapters = [
        'Uploader\\Adapters\\Base64',
        'Uploader\\Adapters\\Psr7',
        'Uploader\\Adapters\\Upload',
        'Uploader\\Adapters\\Url',
    ];

    protected $callbacks = [
        'destination' => null,
        'prefix' => null,
        'overwrite' => null,
        'directory' => null,
        'filename' => null,
        'extension' => null,
    ];

    protected $options = [
        'prefix' => null,
        'overwrite' => false,
        'create_dir' => false,
        'directory' => null,
        'filename' => null,
        'extension' => null,
    ];

    /**
     * @param string $cwd The current working directory used to save the file
     */
    public function __construct($cwd)
    {
        $this->setCwd($cwd);
    }

    /**
     * Execute a upload.
     *
     * @param mixed       $original
     * @param null|string $adapter
     *
     * @throws \InvalidArgumentException On error
     *
     * @return string The file destination
     */
    public function __invoke($original, $adapter = null)
    {
        return $this
            ->with($original, $adapter)
            ->save()
            ->getDestination();
    }

    /**
     * Set the current working directory.
     *
     * @param string $cwd
     *
     * @return $this
     */
    public function setCwd($cwd)
    {
        $this->cwd = $cwd;

        return $this;
    }

    /**
     * Set a prefix for the filenames.
     *
     * @return string|null
     */
    public function getCwd()
    {
        return $this->cwd;
    }

    /**
     * Set a prefix for the filenames.
     *
     * @param string|Closure $prefix
     *
     * @return $this
     */
    public function setPrefix($prefix)
    {
        return $this->setOption('prefix', $prefix);
    }

    /**
     * Set a prefix for the filenames.
     *
     * @return string|null
     */
    public function getPrefix()
    {
        return empty($this->options['prefix']) ? null : $this->options['prefix'];
    }

    /**
     * Set the overwrite configuration.
     *
     * @param bool|Closure $overwrite
     *
     * @return $this
     */
    public function setOverwrite($overwrite)
    {
        return $this->setOption('overwrite', $overwrite);
    }

    /**
     * Get the overwrite configuration.
     *
     * @return bool
     */
    public function getOverwrite()
    {
        return (boolean) $this->options['overwrite'];
    }

    /**
     * Set the create_dir configuration.
     *
     * @param bool|Closure $create_dir
     *
     * @return $this
     */
    public function setCreateDir($create_dir)
    {
        return $this->setOption('create_dir', $create_dir);
    }

    /**
     * Get the create_dir configuration.
     *
     * @return bool
     */
    public function getCreateDir()
    {
        return (boolean) $this->options['create_dir'];
    }

    /**
     * Set the destination of the file. It includes the directory, filename and extension.
     *
     * @param string|Closure $destination
     *
     * @return $this
     */
    public function setDestination($destination)
    {
        if ($destination instanceof Closure) {
            $this->callbacks['destination'] = $destination;
        } else {
            $this->options = self::parsePath($destination) + $this->options;
        }

        return $this;
    }

    /**
     * Returns the file destination.
     *
     * @param bool $absolute Whether or not returns the cwd
     *
     * @return string
     */
    public function getDestination($absolute = false)
    {
        return self::fixPath(($absolute ? '/'.$this->cwd : ''), $this->getDirectory(), $this->getPrefix().$this->getFilename().'.'.$this->getExtension());
    }

    /**
     * Set only the directory of the destination.
     *
     * @param string|Closure $directory
     *
     * @return $this
     */
    public function setDirectory($directory)
    {
        return $this->setOption('directory', $directory);
    }

    /**
     * Get the directory of the destination.
     *
     * @return null|string
     */
    public function getDirectory()
    {
        return empty($this->options['directory']) ? null : $this->options['directory'];
    }

    /**
     * Set only the filename of the destination.
     *
     * @param string|Closure $filename
     *
     * @return $this
     */
    public function setFilename($filename)
    {
        return $this->setOption('filename', $filename);
    }

    /**
     * Get the filename of the destination.
     *
     * @return null|string
     */
    public function getFilename()
    {
        return empty($this->options['filename']) ? null : $this->options['filename'];
    }

    /**
     * Set only the file extension of the destination.
     *
     * @param string|Closure $extension
     *
     * @return $this
     */
    public function setExtension($extension)
    {
        return $this->setOption('extension', $extension);
    }

    /**
     * Get the extension of the destination.
     *
     * @return null|string
     */
    public function getExtension()
    {
        return empty($this->options['extension']) ? null : strtolower($this->options['extension']);
    }

    /**
     * Set the original source.
     *
     * @param mixed       $original
     * @param null|string $adapter
     *
     * @throws \InvalidArgumentException On error
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
     * Save the file.
     *
     * @throws \Exception On error
     *
     * @return $this
     */
    public function save()
    {
        if (!$this->original || empty($this->adapter)) {
            throw new \Exception('Original source is not defined');
        }

        call_user_func("{$this->adapter}::fixDestination", $this, $this->original);

        //Execute callbacks
        foreach ($this->callbacks as $name => $callback) {
            if ($callback) {
                if ($name === 'destination') {
                    $this->setDestination($callback($this));
                } else {
                    $this->options[$name] = $callback($this);
                }
            }
        }

        $destination = $this->getDestination(true);

        if (!$this->getOverwrite() && is_file($destination)) {
            throw new \RuntimeException(sprintf('Cannot override the file "%s"', $destination));
        }

        if ($this->getCreateDir() && !is_dir(dirname($destination))) {
            if (mkdir(dirname($destination), 0777, true) === false) {
                throw new \RuntimeException(sprintf('Unable to create the directory "%s"', dirname($destination)));
            }
        }

        call_user_func("{$this->adapter}::save", $this->original, $destination);

        return $this;
    }

    /**
     * Saves an option.
     *
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    protected function setOption($name, $value)
    {
        if ($value instanceof Closure) {
            $this->callbacks[$name] = $value;
        } else {
            $this->options[$name] = $value;
        }

        return $this;
    }

    /**
     * Helper function used to parse a path.
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
            'extension' => isset($components['extension']) ? $components['extension'] : null,
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
            return self::fixPath(implode('/', func_get_args()));
        }

        $replace = ['#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#'];

        do {
            $path = preg_replace($replace, '/', $path, -1, $n);
        } while ($n > 0);

        return $path;
    }
}
