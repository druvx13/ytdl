<?php
/**
 * webapp/config.php — Server configuration
 *
 * Edit this file when the webapp cannot find yt-dlp automatically.
 * Apache/PHP runs with a stripped PATH (/bin:/usr/bin only), so tools
 * installed in /usr/local/bin or ~/.local/bin are often invisible to it.
 */

/**
 * Absolute path to the yt-dlp (or youtube-dl) executable.
 *
 * Leave as '' to use automatic detection (searches PATH + common directories).
 * Set to the full path if auto-detection still fails, e.g.:
 *   define('YTDLP_EXEC', '/usr/local/bin/yt-dlp');
 *   define('YTDLP_EXEC', '/home/youruser/.local/bin/yt-dlp');
 *   define('YTDLP_EXEC', '/usr/bin/yt-dlp');
 */
define('YTDLP_EXEC', '');
