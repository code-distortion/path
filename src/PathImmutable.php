<?php

namespace CodeDistortion\Path;

/**
 * Represent a path to a directory or file. Is immutable, will produce a new instance when modified.
 */
final class PathImmutable extends AbstractPath implements PathInterface
{
    /** @var boolean Whether objects of this class are immutable or not. */
    protected static bool $immutable = true;
}
