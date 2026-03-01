<?php
/**
 * ytdl_helper.php — Shared helpers for api.php and download.php
 */

declare(strict_types=1);

use Symfony\Component\Process\ExecutableFinder;

/**
 * Resolve the yt-dlp / youtube-dl executable path.
 *
 * Priority:
 *  1. YTDLP_EXEC constant from config.php (if non-empty and executable)
 *  2. Auto-detect: PATH + common extra install directories
 *
 * Returns '' when nothing is found.
 */
function resolveYtdlExec(): string
{
    // 1. Explicit config setting
    if (defined('YTDLP_EXEC') && YTDLP_EXEC !== '' && is_executable(YTDLP_EXEC)) {
        return YTDLP_EXEC;
    }

    // 2. Auto-detect: PATH + common extra directories
    $finder    = new ExecutableFinder();
    $extraDirs = [
        '/usr/local/bin',
        '/usr/bin',
        '/bin',
        '/opt/homebrew/bin', // macOS Homebrew
        '/snap/bin',         // snap packages
    ];

    // Add the running user's ~/.local/bin (pip --user installs) if $HOME is set
    $home = getenv('HOME');
    if ($home !== false && $home !== '') {
        $extraDirs[] = rtrim($home, '/') . '/.local/bin';
    }

    foreach (['yt-dlp', 'youtube-dl'] as $name) {
        $found = $finder->find($name, '', $extraDirs);
        if ($found !== null && $found !== '') {
            return $found;
        }
    }

    return '';
}
