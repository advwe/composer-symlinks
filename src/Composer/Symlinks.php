<?php

namespace SomeWork\Composer;

use Composer\Script\Event;
use Composer\Util\Filesystem;

class Symlinks
{
    /**
     * @param Event $event
     * @throws InvalidArgumentException
     */
    public static function create(Event $event): void
    {
        $fileSystem = new Filesystem();
        $symlinks = static::getSymlinks($event);
        foreach ($symlinks as $target => $link) {
            if ($fileSystem->isAbsolutePath($target)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid symlink target path %s. It must be relative', $target)
                );
            }

            if ($fileSystem->isAbsolutePath($link)) {
                throw new InvalidArgumentException(
                    sprintf('Invalid symlink link path %s. It must be relative', $link)
                );
            }

            $targetPath = getcwd() . DIRECTORY_SEPARATOR . $target;
            $linkPath = getcwd() . DIRECTORY_SEPARATOR . $link;

            if (!is_dir($targetPath)) {
                throw new InvalidArgumentException(
                    sprintf('The target path %s does not exists', $targetPath)
                );
            }

            $event->getIO()->write("  Symlinking <comment>$target</comment> to <comment>$link</comment>");
            $fileSystem->ensureDirectoryExists(dirname($linkPath));
            $fileSystem->relativeSymlink($targetPath, $linkPath);
        }
    }

    protected static function getSymlinks(Event $event)
    {
        $extras = $event->getComposer()->getPackage()->getExtra();

        if (!isset($extras['somework/composer-symlinks'])) {
            throw new InvalidArgumentException('The parameter handler needs to be configured through the extra.somework/composer-symlinks setting.');
        }

        $configs = $extras['somework/composer-symlinks'];

        if (!is_array($configs)) {
            throw new InvalidArgumentException('The extra.somework/composer-symlinks setting must be an array.');
        }

        return array_unique($configs);
    }
}