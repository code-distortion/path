<?php

namespace CodeDistortion\Path;

/**
 * Represent a path to a directory or file.
 */
final class Path extends AbstractPath implements PathInterface
{
    /** @var boolean Whether objects of this class are immutable or not. */
    protected static bool $immutable = false;
}
