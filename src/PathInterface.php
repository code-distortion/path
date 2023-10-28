<?php

namespace CodeDistortion\Path;

/**
 * Represent a path to a directory or file.
 */
interface PathInterface
{
    /**
     * Alternative constructor.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return self|static
     */
    public static function new(string|AbstractPath $path, bool $blockBreakout = true): self|static;

    /**
     * Alternative constructor - for a path that is a directory.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return self|static
     */
    public static function newDir(string|AbstractPath $path, bool $blockBreakout = true): self|static;

    /**
     * Alternative constructor - for a path that is a for a file.
     *
     * @param string|AbstractPath $path          The path to use.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return self|static
     */
    public static function newFile(string|AbstractPath $path, bool $blockBreakout = true): self|static;



    /**
     * Make a clone of this instance.
     *
     * @return static
     */
    public function copy(): static;

    /**
     * Add a child directory or file to the path.
     *
     * @param string|AbstractPath $path          The path to add.
     * @param boolean             $blockBreakout Whether to allow the path to break out of the base dir using .. or not.
     * @return static
     */
    public function add(string|AbstractPath $path, bool $blockBreakout = true): static;



    /**
     * Resolve "." and ".." in the path. Also removes unnecessary slashes.
     *
     * @return static
     */
    public function resolve(): static;

    /**
     * Specify the directory separator to use (will fall back to DIRECTORY_SEPARATOR when null).
     *
     * @param string|null $separator The directory separator to use.
     * @return static
     */
    public function separator(?string $separator): static;



    /**
     * Render the path as a string.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Get the directory portion of the path.
     *
     * @return self|static
     */
    public function getDir(): self|static;

    /**
     * Get the filename portion of the path.
     *
     * @param boolean $includeExtension Whether to include the extension or not.
     * @return string|null
     */
    public function getFilename(bool $includeExtension = true): ?string;

    /**
     * Get the filename extension portion of the path.
     *
     * @param boolean $includeDot Whether to include the '.' in the extension or not.
     * @return string|null
     */
    public function getExtension(bool $includeDot = true): ?string;

    /**
     * Check if this path is absolute or not.
     *
     * @return boolean
     */
    public function isAbsolute(): bool;

    /**
     * Check if this path is relative or not.
     *
     * @return boolean
     */
    public function isRelative(): bool;
}
