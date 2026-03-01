<?php
/**
 * api.php — JSON API endpoint
 *
 * Handles:
 *   action=info   : Extract video/playlist info and return available formats
 *   action=check  : Check whether yt-dlp / youtube-dl is available on the server
 */

declare(strict_types=1);

// Prevent any PHP warnings/notices from appearing in the JSON response,
// regardless of the server's php.ini (no .htaccess required).
ini_set('display_errors', '0');
ini_set('log_errors', '1');

require_once __DIR__ . '/vendor/autoload.php';

use Flatgreen\Ytdl\Options;
use Flatgreen\Ytdl\Ytdl;

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// --------------------------------------------------------------------------
// Helpers
// --------------------------------------------------------------------------

function jsonError(string $message, int $code = 400): void
{
    http_response_code($code);
    echo json_encode(['error' => $message]);
    exit;
}

function formatBytes(?int $bytes): string
{
    if ($bytes === null || $bytes <= 0) {
        return 'unknown';
    }
    $units = ['B', 'KB', 'MB', 'GB'];
    $i = 0;
    while ($bytes >= 1024 && $i < count($units) - 1) {
        $bytes /= 1024;
        $i++;
    }
    return round($bytes, 1) . ' ' . $units[$i];
}

function formatDuration(?int $seconds): string
{
    if ($seconds === null || $seconds <= 0) {
        return 'unknown';
    }
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    $s = $seconds % 60;
    if ($h > 0) {
        return sprintf('%d:%02d:%02d', $h, $m, $s);
    }
    return sprintf('%d:%02d', $m, $s);
}

function sanitizeFormats(array $formats): array
{
    $result = [];
    foreach ($formats as $fmt) {
        $formatId  = $fmt['format_id'] ?? '';
        $ext       = $fmt['ext'] ?? '';
        $vcodec    = $fmt['vcodec'] ?? 'none';
        $acodec    = $fmt['acodec'] ?? 'none';
        $height    = $fmt['height'] ?? null;
        $fps       = $fmt['fps'] ?? null;
        $tbr       = isset($fmt['tbr'])  ? (float)$fmt['tbr']  : null;
        $abr       = isset($fmt['abr'])  ? (float)$fmt['abr']  : null;
        $filesize  = $fmt['filesize'] ?? $fmt['filesize_approx'] ?? null;
        $note      = $fmt['format_note'] ?? '';

        // Determine stream type
        $hasVideo = ($vcodec !== 'none' && !empty($vcodec));
        $hasAudio = ($acodec !== 'none' && !empty($acodec));

        if (!$hasVideo && !$hasAudio) {
            continue; // skip useless formats
        }

        $type = 'video+audio';
        if ($hasVideo && !$hasAudio) {
            $type = 'video';
        } elseif (!$hasVideo && $hasAudio) {
            $type = 'audio';
        }

        // Human-readable resolution
        $resolution = 'audio only';
        if ($hasVideo && $height) {
            $resolution = $height . 'p';
            if ($fps && $fps > 30) {
                $resolution .= $fps . 'fps';
            }
        } elseif ($hasVideo) {
            $resolution = $fmt['resolution'] ?? $note ?: 'unknown';
        }

        $result[] = [
            'format_id'  => $formatId,
            'ext'        => $ext,
            'type'       => $type,
            'resolution' => $resolution,
            'height'     => $height,
            'fps'        => $fps,
            'tbr'        => $tbr,
            'abr'        => $abr,
            'filesize'   => $filesize,
            'filesize_hr'=> formatBytes($filesize),
            'vcodec'     => $vcodec,
            'acodec'     => $acodec,
            'note'       => $note,
        ];
    }

    // Sort: video+audio first by height desc, then video-only, then audio-only
    usort($result, function (array $a, array $b) {
        $order = ['video+audio' => 0, 'video' => 1, 'audio' => 2];
        $ta    = $order[$a['type']] ?? 3;
        $tb    = $order[$b['type']] ?? 3;
        if ($ta !== $tb) {
            return $ta - $tb;
        }
        return (int)($b['height'] ?? 0) - (int)($a['height'] ?? 0);
    });

    return $result;
}

// --------------------------------------------------------------------------
// Input validation
// --------------------------------------------------------------------------

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// --------------------------------------------------------------------------
// action=check : verify yt-dlp/youtube-dl availability
// --------------------------------------------------------------------------
if ($action === 'check') {
    $options = new Options();
    $ytdl    = new Ytdl($options);
    $exec    = $ytdl->getYtdlExecPath();
    $name    = $ytdl->getYtdlExecName();

    if (empty($exec)) {
        echo json_encode([
            'available' => false,
            'message'   => 'Neither yt-dlp nor youtube-dl was found in PATH.',
        ]);
    } else {
        echo json_encode([
            'available' => true,
            'name'      => $name,
            'path'      => $exec,
        ]);
    }
    exit;
}

// --------------------------------------------------------------------------
// action=info : extract video information
// --------------------------------------------------------------------------
if ($action === 'info') {
    $url = trim($_GET['url'] ?? $_POST['url'] ?? '');

    if (empty($url)) {
        jsonError('URL is required.');
    }

    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        jsonError('Invalid URL format.');
    }

    // Basic YouTube / common video-site URL guard
    $allowedHosts = [
        'youtube.com', 'www.youtube.com', 'youtu.be',
        'm.youtube.com', 'music.youtube.com',
    ];
    $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
    if (!in_array($host, $allowedHosts, true)) {
        jsonError('Only YouTube URLs are supported.');
    }

    $cacheDir = __DIR__ . '/cache';
    if (!is_dir($cacheDir)) {
        @mkdir($cacheDir, 0755, true);
    }

    try {
        $options = new Options();
        $ytdl    = new Ytdl($options);
        $ytdl->setCache(['directory' => $cacheDir, 'duration' => 3600]);

        $info = $ytdl->extractInfos($url);

        if (empty($info)) {
            $errors = $ytdl->getErrors();
            jsonError('Could not extract video info. ' . implode(' ', $errors), 500);
        }

        // Build response
        $response = [
            'id'          => $info['id'] ?? '',
            'title'       => $info['title'] ?? 'Unknown title',
            'thumbnail'   => $info['thumbnail'] ?? '',
            'duration'    => $info['duration'] ?? 0,
            'duration_hr' => formatDuration((int)($info['duration'] ?? 0)),
            'uploader'    => $info['uploader'] ?? '',
            'view_count'  => $info['view_count'] ?? 0,
            'webpage_url' => $info['webpage_url'] ?? $url,
            'is_playlist' => $ytdl->isPlaylist($info),
            'formats'     => [],
        ];

        if ($ytdl->isPlaylist($info)) {
            $response['entries'] = [];
            foreach (($info['entries'] ?? []) as $entry) {
                $response['entries'][] = [
                    'id'        => $entry['id'] ?? '',
                    'title'     => $entry['title'] ?? '',
                    'thumbnail' => $entry['thumbnail'] ?? '',
                    'duration'  => $entry['duration'] ?? 0,
                    'duration_hr' => formatDuration((int)($entry['duration'] ?? 0)),
                    'webpage_url'=> $entry['webpage_url'] ?? '',
                ];
            }
        } else {
            if (!empty($info['formats'])) {
                $response['formats'] = sanitizeFormats($info['formats']);
            } elseif (!empty($info['url'])) {
                // Single-format (no split formats array)
                $response['formats'] = [[
                    'format_id'  => $info['format_id'] ?? 'best',
                    'ext'        => $info['ext'] ?? 'mp4',
                    'type'       => 'video+audio',
                    'resolution' => ($info['height'] ?? '') ? $info['height'] . 'p' : 'best',
                    'height'     => $info['height'] ?? null,
                    'fps'        => $info['fps'] ?? null,
                    'tbr'        => null,
                    'abr'        => null,
                    'filesize'   => null,
                    'filesize_hr'=> 'unknown',
                    'vcodec'     => $info['vcodec'] ?? '',
                    'acodec'     => $info['acodec'] ?? '',
                    'note'       => '',
                ]];
            }
        }

        echo json_encode($response);
    } catch (\Throwable $e) {
        jsonError('Server error: ' . $e->getMessage(), 500);
    }
    exit;
}

// Fallback — unknown action
jsonError('Unknown action.', 400);
