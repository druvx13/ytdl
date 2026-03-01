<?php
/**
 * download.php — Downloads a YouTube video/audio and streams it to the browser.
 *
 * Expects POST params:
 *   url         : The YouTube page URL
 *   format_id   : yt-dlp format ID (e.g. "137+140", "18", "bestvideo+bestaudio")
 *   output_type : (optional) container to recode into: mp4 | webm | mkv | mp3 | m4a | ogg | opus | wav | flac
 *   audio_only  : (optional) "1" to extract audio only
 */

declare(strict_types=1);

// Prevent any PHP warnings/notices from corrupting the file stream,
// regardless of the server's php.ini (no .htaccess required).
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/ytdl_helper.php';

use Flatgreen\Ytdl\Options;
use Flatgreen\Ytdl\Ytdl;

// ── Security headers ────────────────────────────────────────────────────────
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');

// ── Input validation ─────────────────────────────────────────────────────────
// Note: void used instead of never for PHP 7.4/8.0 compatibility (never = PHP 8.1+)
function abort(string $msg, int $code = 400): void
{
    http_response_code($code);
    header('Content-Type: text/plain; charset=utf-8');
    exit($msg);
}

$url        = trim($_POST['url']         ?? '');
$formatId   = trim($_POST['format_id']   ?? 'bestvideo+bestaudio/best');
$outputType = trim($_POST['output_type'] ?? '');
$audioOnly  = ($_POST['audio_only']      ?? '0') === '1';

if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    abort('Invalid or missing URL.');
}

// Restrict to YouTube
$allowedHosts = [
    'youtube.com', 'www.youtube.com', 'youtu.be',
    'm.youtube.com', 'music.youtube.com',
];
$host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
if (!in_array($host, $allowedHosts, true)) {
    abort('Only YouTube URLs are supported.');
}

// Whitelist format IDs — allow alphanumerics, +, /, -, _, ., and spaces
if (!preg_match('/^[a-zA-Z0-9\+\/\-_\.\s]+$/', $formatId)) {
    abort('Invalid format ID.');
}

// Whitelist output types
$allowedVideoTypes = ['mp4', 'webm', 'mkv', 'avi'];
$allowedAudioTypes = ['mp3', 'm4a', 'ogg', 'opus', 'wav', 'flac'];
$allowedTypes      = array_merge($allowedVideoTypes, $allowedAudioTypes);

if ($outputType !== '' && !in_array($outputType, $allowedTypes, true)) {
    abort('Invalid output type.');
}

// If outputType is an audio type, force audio-only mode
if (in_array($outputType, $allowedAudioTypes, true)) {
    $audioOnly = true;
}

// ── Temp download directory (outside web root is ideal; this is under downloads/) ──
$cacheDir   = __DIR__ . '/cache';
$downloadId = bin2hex(random_bytes(16));
$dlDir      = __DIR__ . '/downloads/' . $downloadId . '/';

foreach ([$cacheDir, $dlDir] as $dir) {
    if (!is_dir($dir) && !@mkdir($dir, 0755, true)) {
        abort('Could not create required directory.', 500);
    }
}

// ── Build yt-dlp options ─────────────────────────────────────────────────────
$options = new Options();
$options->addOptions(['-f' => $formatId]);

if ($audioOnly) {
    $options->addOptions(['-x']);
    if ($outputType !== '' && in_array($outputType, $allowedAudioTypes, true)) {
        $options->addOptions(['--audio-format' => $outputType]);
        $options->addOptions(['--audio-quality' => '0']);
    }
} elseif ($outputType !== '' && in_array($outputType, $allowedVideoTypes, true)) {
    $options->addOptions(['--merge-output-format' => $outputType]);
}

// ── Execute download ─────────────────────────────────────────────────────────
set_time_limit(0);
ignore_user_abort(false);

$ytdlExec = resolveYtdlExec();
$ytdl = $ytdlExec !== '' ? new Ytdl($options, null, $ytdlExec) : new Ytdl($options);
$ytdl->setCache(['directory' => $cacheDir, 'duration' => 3600]);

try {
    $infoDict = $ytdl->download($url, $dlDir);
} catch (\Throwable $e) {
    cleanup($dlDir);
    abort('Download failed: ' . $e->getMessage(), 500);
}

$errors = $ytdl->getErrors();

// ── Find downloaded file ─────────────────────────────────────────────────────
$files = array_values(array_filter(glob($dlDir . '*') ?: [], 'is_file'));

if (empty($files)) {
    cleanup($dlDir);
    $detail = !empty($errors) ? implode(' | ', $errors) : 'yt-dlp produced no output file.';
    abort('Download failed: ' . $detail, 500);
}

$filePath = $files[0];
$fileSize = filesize($filePath);
$rawName  = basename($filePath);
// Sanitize filename for Content-Disposition header
$safeName = preg_replace('/[^\w\-. ()[\]]+/u', '_', $rawName) ?: 'download';

// Derive MIME type
$ext      = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
$mimeMap  = [
    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',
    'mkv'  => 'video/x-matroska',
    'avi'  => 'video/x-msvideo',
    'mp3'  => 'audio/mpeg',
    'm4a'  => 'audio/mp4',
    'ogg'  => 'audio/ogg',
    'opus' => 'audio/opus',
    'wav'  => 'audio/wav',
    'flac' => 'audio/flac',
];
$mime = $mimeMap[$ext] ?? (mime_content_type($filePath) ?: 'application/octet-stream');

// ── Stream to browser ────────────────────────────────────────────────────────
header('Content-Type: ' . $mime);
header('Content-Disposition: attachment; filename="' . addslashes($safeName) . '"');
if ($fileSize !== false && $fileSize > 0) {
    header('Content-Length: ' . $fileSize);
}
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($filePath);

// ── Cleanup ──────────────────────────────────────────────────────────────────
cleanup($dlDir);
exit;

// ────────────────────────────────────────────────────────────────────────────
function cleanup(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }
    foreach (glob($dir . '*') ?: [] as $f) {
        if (is_file($f)) {
            @unlink($f);
        }
    }
    @rmdir($dir);
}
