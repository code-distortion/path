<?php

namespace CodeDistortion\Path;

/**
 * Represent a path to a directory or file.
 */
abstract class AbstractPath implements PathInterface
{
    /** @var string The full path. */
    private string $path;

    /** @var boolean Whether breakout is allowed or not. */
    private bool $blockBreakout;

    /** @var string|null The directory separator to use when rendering output - falls back to DIRECTORY_SEPARATOR */
    private ?string $separator = null;

    /** @var boolean Whether objects of this class are immutable or not. */
    protected static bool $immutable = false;



    /**
     * Constructor - cannot be called directly.
     *
     * Detects if it's absolute (starts with a "/") or not.
     * Detects if it's a directory (ends with a "/") or not.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     */
    final protected function __construct(string|AbstractPath $path, bool $blockBreakout)
    {
        $this->store($path, $blockBreakout);
    }

    /**
     * Alternative constructor.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return static
     */
    public static function new(string|AbstractPath $path, bool $blockBreakout = true): static
    {
        return new static($path, $blockBreakout);
    }

    /**
     * Alternative constructor - for a path that is a directory.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return static
     */
    public static function newDir(string|AbstractPath $path, bool $blockBreakout = true): static
    {
        $return = static::new($path, $blockBreakout);

        $return->path = ($return->path !== '') && !str_ends_with($return->path, '/')
            ? "$return->path/"
            : $return->path;

        return $return;
    }

    /**
     * Alternative constructor - for a path that is a for a file.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return static
     */
    public static function newFile(string|AbstractPath $path, bool $blockBreakout = true): static
    {
        $return = static::new($path, $blockBreakout);

        $isAbsolute = str_starts_with($return->path, '/');

        $return->path = rtrim($return->path, '/');

        if (($return->path === '') && ($isAbsolute)) {
            $return->path = '/';
        }

        return $return;
    }



    /**
     * Create a new cloned instance of this object if immutable is turned on.
     *
     * @return static
     */
    private function immute(): static
    {
        return static::$immutable
            ? $this->copy()
            : $this;
    }

    /**
     * Make a clone of this instance.
     *
     * @return static
     */
    public function copy(): static
    {
        return clone $this;
    }

    /**
     * Copy the settings from another Path instance.
     *
     * @param AbstractPath $path The Path to copy from.
     * @return $this
     */
    private function copySettingsFrom(AbstractPath $path): static
    {
        $this->blockBreakout = $path->blockBreakout;
        $this->separator = $path->separator;

        return $this;
    }



    /**
     * Add a child directory or file to the path.
     *
     * @param string|AbstractPath $path          The path to add.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return static
     */
    public function add(string|AbstractPath $path, bool $blockBreakout = true): static
    {
        $path = $path !== '/'
            ? $path
            : '';

        $baseDir = $this->getDir();
        $childPath = static::new($path, $blockBreakout)->copySettingsFrom($this);
        $newPath = "$baseDir$childPath";

        $return = $this->immute();
        $return->store($newPath, $this->blockBreakout);
        return $return;
    }



    /**
     * Normalise the path (so it's interpreted the same on all OS's), make sure it doesn't break out if it's now allowed
     * to, and store it.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return void
     */
    private function store(string|AbstractPath $path, bool $blockBreakout): void
    {
        $path = str_replace('\\', '/', $path);

        if ($blockBreakout) {
            $path = $this->removeBreakoutDotDirsFromString($path);
        }

        $this->blockBreakout = $blockBreakout;
        $this->path = $path;
    }



    /**
     * Resolve "." and ".." in the path. Also removes unnecessary slashes.
     *
     * @return static
     */
    public function resolve(): static
    {
        $newPath = $this->removeDotDirsFromString($this->path);

        $return = $this->immute();
        $return->store($newPath, $this->blockBreakout);
        return $return;
    }

    /**
     * Specify the directory separator to use (will fall back to DIRECTORY_SEPARATOR when null).
     *
     * @param string|null $separator The directory separator to use.
     * @return static
     */
    public function separator(?string $separator): static
    {
        $return = $this->immute();
        $return->separator = $separator;
        return $return;
    }



    /**
     * Render the path as a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->convertForCurrentOS($this->path);
    }

    /**
     * Convert the directory separators for the current os.
     *
     * @param string $path The path to convert.
     * @return string
     */
    private function convertForCurrentOS(string $path): string
    {
        $separator = $this->separator ?? DIRECTORY_SEPARATOR;
        return str_replace('/', $separator, $path);
    }

    /**
     * Get the directory portion of the path.
     *
     * @return static
     */
    public function getDir(): static
    {
        $path = $this->path;

        // lop off the filename
        if (!str_ends_with($this->path, '/')) {

            $temp = explode('/', $this->path);

            // remove the filename, provided it's not '.' or '..'
            $last = end($temp);
            if (!in_array($last, ['.', '..'], true)) {
                array_pop($temp);

                $temp[] = ''; // add the trailing slash back in
                $path = implode('/', $temp);
            }
        }

        return static::new($path)->copySettingsFrom($this);
    }

    /**
     * Get the filename portion of the path.
     *
     * @param boolean $includeExtension Whether to include the extension or not.
     * @return string|null
     */
    public function getFilename(bool $includeExtension = true): ?string
    {
        if ($this->path === '') {
            return null;
        }

        if (str_ends_with($this->path, '/')) {
            return null;
        }

        $parts = explode('/', $this->path);
        $filename = array_pop($parts);

        if (!$includeExtension) {

            $parts = explode('.', $filename);
            if (count($parts) > 1) {
                array_pop($parts);
            }
            $filename = implode('.', $parts);
        }

        return !in_array($filename, ['.', '..'], true)
            ? $filename
            : null;
    }

    /**
     * Get the filename extension portion of the path.
     *
     * @param boolean $includeDot Whether to include the '.' in the extension or not.
     * @return string|null
     */
    public function getExtension(bool $includeDot = true): ?string
    {
        $filename = $this->getFilename(true);
        if (is_null($filename)) {
            return null;
        }

        $parts = explode('.', $filename);
        if (count($parts) <= 1) {
            return null;
        }

        $extension = array_pop($parts);
        return $includeDot
            ? ".$extension"
            : $extension;
    }

    /**
     * Check if this path is absolute or not.
     *
     * @return boolean
     */
    public function isAbsolute(): bool
    {
        return str_starts_with($this->path, '/');
    }

    /**
     * Check if this path is relative or not.
     *
     * @return boolean
     */
    public function isRelative(): bool
    {
        return !$this->isAbsolute();
    }



    /**
     * Remove "." and ".." from the path, that would break out from the root.
     *
     * @param string $path The path to normalise.
     * @return string
     */
    private function removeBreakoutDotDirsFromString(string $path): string
    {
        // remove "." and ".." from the path
        $parts = explode('/', $path);
        $newParts = [];
        $depth = 0;
        $count = 0;
        foreach ($parts as $part) {

            $isLast = (++$count === count($parts));

            if ($part === '.') {
                $newParts[] = $part;
                continue;
            }

            if ($part === '..') {
                if ($depth > 0) {
                    $newParts[] = $part;
                    $depth--;
                } else {
                    if ($isLast) {
                        $newParts[] = '';
                    }
                }
                continue;
            }

            $newParts[] = $part;
            if ($newParts !== ['']) {
                $depth++;
            }
        }

        return implode('/', $newParts);
    }

    /**
     * Remove all "." and ".." from the path.
     *
     * @param string $path The path to normalise.
     * @return string
     */
    private function removeDotDirsFromString(string $path): string
    {
        // remove "." and ".." from the path
        $parts = explode('/', $path);
        $newParts = [];
        // @infection-ignore-all - FalseValue - initialisation of $latestPartWasDotDot, but doesn't affect the outcome
        $latestPartWasDotDot = false;
        foreach ($parts as $part) {

            $latestPartWasDotDot = false;

            if ($part === '.') {
                if (count($newParts) > 0) {
                    $newParts[] = '';
                }
                continue;
            }

            if ($part === '..') {
                array_pop($newParts);
                $latestPartWasDotDot = true;
                continue;
            }
            $newParts[] = $part;
        }

        if ($latestPartWasDotDot) {
            $newParts[] = '';
        }

        $path = implode('/', $newParts);

        return preg_replace('#//+#', '/', $path) ?? '';
    }
}
