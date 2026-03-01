<?php
/**
 * webapp/config.php — Server configuration
 *
 * Edit this file when the webapp cannot find yt-dlp automatically.
 *
 * ── Easiest option for shared hosting (e.g. cPanel / restricted /htdocs) ──
 *
 * Download the yt-dlp binary for your server's OS/arch from:
 *   https://github.com/yt-dlp/yt-dlp/releases/latest
 *
 * Upload it to:
 *   webapp/bin/yt-dlp          ← Linux / macOS
 *   webapp/bin/yt-dlp.exe      ← Windows
 *
 * Then make it executable (Linux/macOS):
 *   chmod +x webapp/bin/yt-dlp
 *
 * The app will find it automatically — no changes to this file needed.
 *
 * ── Alternative: set an absolute path ────────────────────────────────────
 *
 * If yt-dlp is installed elsewhere and auto-detection still fails, set the
 * full path below, e.g.:
 *   define('YTDLP_EXEC', '/usr/local/bin/yt-dlp');
 *   define('YTDLP_EXEC', '/home/youruser/.local/bin/yt-dlp');
 */
define('YTDLP_EXEC', '');
