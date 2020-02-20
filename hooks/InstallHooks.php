<?php

declare(strict_types=1);

namespace R2D2\Hooks;

use Composer\Script\Event;
use Symfony\Component\Filesystem\Filesystem;

class InstallHooks
{
    public static function checkHooks(Event $event): bool
    {
        $result = true;
        if ($event->isDevMode()) {
            $result = self::installPrecommit($event);
        }

        return $result;
    }

    private static function installPrecommit(Event $event)
    {
        $io = $event->getIO();

        $filesystem = new Filesystem();
        if ($filesystem->exists('.git/hooks')) {
            $io->write('<info>Removing current .git/hooks path</info>');
            $filesystem->remove('.git/hooks');
        }

        $io->write('<info>Installing pre-commit hook</info>');
        $filesystem->symlink('../hooks', '.git/hooks');

        return true;
    }
}
