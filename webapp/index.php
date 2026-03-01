<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>YT Downloader</title>
<meta name="description" content="Download YouTube videos in any quality or as audio — fast and free.">
<!-- Favicon inline SVG -->
<link rel="icon" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>▶</text></svg>">
<style>
/* ── Reset & variables ────────────────────────────────────────────────────── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0f1117;--surface:#1a1d27;--surface2:#22263a;--border:#2e3248;
  --accent:#ff0033;--accent-hover:#cc0029;--accent2:#4f46e5;
  --text:#e8eaf6;--text-muted:#8892b0;--text-dim:#505875;
  --green:#22c55e;--orange:#f59e0b;--radius:12px;--radius-sm:8px;
  --shadow:0 4px 24px rgba(0,0,0,.5);--transition:.2s ease;
}
[data-theme="light"]{
  --bg:#f0f2f8;--surface:#fff;--surface2:#eef0f8;--border:#d1d5e8;
  --text:#1e2040;--text-muted:#4a5580;--text-dim:#8892b0;
  --shadow:0 4px 24px rgba(0,0,0,.08);
}

/* ── Base ─────────────────────────────────────────────────────────────────── */
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--text);font-family:'Segoe UI',system-ui,sans-serif;
     min-height:100vh;line-height:1.6;transition:background var(--transition),color var(--transition)}
a{color:var(--accent);text-decoration:none}
a:hover{text-decoration:underline}
img{max-width:100%;display:block}
button{cursor:pointer;font-family:inherit}

/* ── Layout ───────────────────────────────────────────────────────────────── */
.container{max-width:860px;margin:0 auto;padding:0 16px}

/* ── Header ───────────────────────────────────────────────────────────────── */
header{background:var(--surface);border-bottom:1px solid var(--border);
       position:sticky;top:0;z-index:100;backdrop-filter:blur(8px)}
.header-inner{display:flex;align-items:center;justify-content:space-between;padding:14px 0}
.logo{display:flex;align-items:center;gap:10px;font-size:1.25rem;font-weight:700;color:var(--text)}
.logo svg{flex-shrink:0}
.logo span em{color:var(--accent);font-style:normal}
.theme-btn{background:none;border:1px solid var(--border);border-radius:var(--radius-sm);
           padding:6px 10px;color:var(--text-muted);font-size:1rem;transition:all var(--transition)}
.theme-btn:hover{border-color:var(--accent);color:var(--accent)}

/* ── Hero / search ────────────────────────────────────────────────────────── */
.hero{padding:48px 0 36px;text-align:center}
.hero h1{font-size:clamp(1.6rem,4vw,2.5rem);font-weight:800;margin-bottom:10px;
         background:linear-gradient(135deg,var(--text) 40%,var(--accent));
         -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.hero p{color:var(--text-muted);font-size:1rem;margin-bottom:32px}

.search-box{display:flex;gap:8px;background:var(--surface);border:1.5px solid var(--border);
            border-radius:var(--radius);padding:6px 6px 6px 16px;
            transition:border-color var(--transition);max-width:680px;margin:0 auto}
.search-box:focus-within{border-color:var(--accent)}
.search-box input{flex:1;background:none;border:none;outline:none;color:var(--text);
                  font-size:1rem;min-width:0}
.search-box input::placeholder{color:var(--text-dim)}
.btn{display:inline-flex;align-items:center;gap:6px;border:none;border-radius:var(--radius-sm);
     padding:10px 20px;font-size:.95rem;font-weight:600;transition:all var(--transition)}
.btn-primary{background:var(--accent);color:#fff}
.btn-primary:hover:not(:disabled){background:var(--accent-hover);transform:translateY(-1px)}
.btn-secondary{background:var(--surface2);color:var(--text);border:1px solid var(--border)}
.btn-secondary:hover:not(:disabled){border-color:var(--accent);color:var(--accent)}
.btn-success{background:var(--green);color:#fff}
.btn-success:hover:not(:disabled){filter:brightness(1.1);transform:translateY(-1px)}
.btn:disabled{opacity:.5;cursor:not-allowed;transform:none!important}

/* ── Alert banner ─────────────────────────────────────────────────────────── */
.alert{border-radius:var(--radius-sm);padding:12px 16px;font-size:.9rem;margin:12px 0;
       display:flex;align-items:flex-start;gap:10px}
.alert-error{background:#3b0c14;border:1px solid #7f1d2a;color:#fca5a5}
.alert-warn{background:#3b2a0a;border:1px solid #78400f;color:#fcd34d}
.alert-info{background:#0c1f3b;border:1px solid #1e40af;color:#93c5fd}
.alert-success{background:#0a2e18;border:1px solid #166534;color:#86efac}
[data-theme="light"] .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
[data-theme="light"] .alert-warn{background:#fef3c7;border-color:#fcd34d;color:#92400e}
[data-theme="light"] .alert-info{background:#dbeafe;border-color:#93c5fd;color:#1e40af}
[data-theme="light"] .alert-success{background:#dcfce7;border-color:#86efac;color:#166534}

/* ── Card ─────────────────────────────────────────────────────────────────── */
.card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);
      overflow:hidden;margin-bottom:24px;box-shadow:var(--shadow)}
.card-header{padding:16px 20px;border-bottom:1px solid var(--border);font-weight:600;
             display:flex;align-items:center;gap:8px;font-size:.95rem}
.card-body{padding:20px}

/* ── Video info card ──────────────────────────────────────────────────────── */
.video-info{display:grid;grid-template-columns:160px 1fr;gap:20px;align-items:start}
@media(max-width:480px){.video-info{grid-template-columns:1fr}}
.video-thumb{border-radius:var(--radius-sm);overflow:hidden;aspect-ratio:16/9;
             background:var(--surface2);position:relative}
.video-thumb img{width:100%;height:100%;object-fit:cover}
.thumb-play{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
            background:rgba(0,0,0,.4);opacity:0;transition:opacity var(--transition)}
.video-thumb:hover .thumb-play{opacity:1}
.video-meta h2{font-size:1.05rem;font-weight:700;margin-bottom:8px;line-height:1.4}
.meta-badges{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px}
.badge{display:inline-block;padding:2px 8px;border-radius:999px;font-size:.75rem;font-weight:600}
.badge-gray{background:var(--surface2);color:var(--text-muted)}
.badge-red{background:#3b0c14;color:#fca5a5}
[data-theme="light"] .badge-red{background:#fee2e2;color:#991b1b}
.meta-uploader{font-size:.85rem;color:var(--text-muted)}

/* ── Format selector ──────────────────────────────────────────────────────── */
.tabs{display:flex;gap:4px;border-bottom:1px solid var(--border);margin-bottom:20px;
      overflow-x:auto;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.tabs::-webkit-scrollbar{display:none}
.tab{flex-shrink:0;padding:8px 18px;border:none;background:none;color:var(--text-muted);
     font-size:.9rem;font-weight:500;border-bottom:2px solid transparent;
     cursor:pointer;transition:all var(--transition)}
.tab.active{color:var(--accent);border-bottom-color:var(--accent)}
.tab:hover:not(.active){color:var(--text)}
.tab-panel{display:none}
.tab-panel.active{display:block}

.form-row{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px}
@media(max-width:500px){.form-row{grid-template-columns:1fr}}
.form-group{display:flex;flex-direction:column;gap:6px}
.form-group label{font-size:.85rem;font-weight:500;color:var(--text-muted)}
.form-select{background:var(--surface2);border:1.5px solid var(--border);border-radius:var(--radius-sm);
             padding:9px 12px;color:var(--text);font-size:.9rem;width:100%;outline:none;
             transition:border-color var(--transition)}
.form-select:focus{border-color:var(--accent)}
.form-select option{background:var(--surface2)}

/* ── Format table ─────────────────────────────────────────────────────────── */
.format-table-wrap{overflow-x:auto}
.format-table{width:100%;border-collapse:collapse;font-size:.85rem}
.format-table th{text-align:left;padding:8px 10px;color:var(--text-muted);font-weight:600;
                 border-bottom:1px solid var(--border);white-space:nowrap}
.format-table td{padding:8px 10px;border-bottom:1px solid var(--border);vertical-align:middle}
.format-table tr:last-child td{border-bottom:none}
.format-table tr.selectable{cursor:pointer;transition:background var(--transition)}
.format-table tr.selectable:hover{background:var(--surface2)}
.format-table tr.selected{background:color-mix(in srgb,var(--accent) 12%,transparent)}
.format-table input[type=radio]{accent-color:var(--accent)}
.fmt-type{display:inline-block;padding:2px 6px;border-radius:4px;font-size:.72rem;
          font-weight:600;text-transform:uppercase;white-space:nowrap}
.fmt-va{background:#1a2a4a;color:#60a5fa}[data-theme="light"] .fmt-va{background:#dbeafe;color:#1d4ed8}
.fmt-v {background:#2a1a1a;color:#f87171}[data-theme="light"] .fmt-v{background:#fee2e2;color:#b91c1c}
.fmt-a {background:#1a2a1a;color:#86efac}[data-theme="light"] .fmt-a{background:#dcfce7;color:#166534}

/* ── Download button area ─────────────────────────────────────────────────── */
.dl-action{display:flex;align-items:center;justify-content:space-between;
           flex-wrap:wrap;gap:12px;margin-top:20px;padding-top:20px;
           border-top:1px solid var(--border)}
.dl-selected-info{font-size:.85rem;color:var(--text-muted)}
.dl-selected-info strong{color:var(--text)}

/* ── Spinner ──────────────────────────────────────────────────────────────── */
.spinner{display:inline-block;width:18px;height:18px;border:2.5px solid rgba(255,255,255,.25);
         border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* ── Progress bar ─────────────────────────────────────────────────────────── */
.progress-wrap{margin-top:16px;display:none}
.progress-wrap.visible{display:block}
.progress-label{font-size:.85rem;color:var(--text-muted);margin-bottom:6px;
                display:flex;justify-content:space-between}
.progress-bar{height:6px;background:var(--surface2);border-radius:999px;overflow:hidden}
.progress-fill{height:100%;background:linear-gradient(90deg,var(--accent),var(--accent2));
               border-radius:999px;animation:progress-indeterminate 1.5s ease infinite}
@keyframes progress-indeterminate{0%{width:0;margin-left:0}50%{width:60%;margin-left:20%}100%{width:0;margin-left:100%}}

/* ── Footer ───────────────────────────────────────────────────────────────── */
footer{text-align:center;padding:32px 16px;color:var(--text-dim);font-size:.8rem;
       border-top:1px solid var(--border);margin-top:40px}
footer a{color:var(--text-muted)}

/* ── Utility ──────────────────────────────────────────────────────────────── */
.hidden{display:none!important}
.text-center{text-align:center}
.mt-8{margin-top:8px}
.mt-16{margin-top:16px}
.w-full{width:100%}

/* ── Warning for no-JS ────────────────────────────────────────────────────── */
noscript{display:block;padding:12px 16px;background:#3b2a0a;border:1px solid #78400f;
         color:#fcd34d;border-radius:var(--radius-sm);margin:12px 0;font-size:.9rem}
</style>
</head>
<body>

<!-- ── Header ─────────────────────────────────────────────────────────────── -->
<header>
  <div class="container">
    <div class="header-inner">
      <div class="logo">
        <svg width="28" height="28" viewBox="0 0 28 28" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect width="28" height="28" rx="6" fill="#ff0033"/>
          <polygon points="11,8 21,14 11,20" fill="white"/>
        </svg>
        <span>YT<em>DL</em></span>
      </div>
      <button class="theme-btn" id="themeToggle" title="Toggle theme" aria-label="Toggle light/dark theme">🌙</button>
    </div>
  </div>
</header>

<!-- ── Main ───────────────────────────────────────────────────────────────── -->
<main>
  <div class="container">

    <!-- Hero -->
    <section class="hero">
      <h1>YouTube Video Downloader</h1>
      <p>Choose your quality, format, and type — video or audio — in seconds.</p>

      <noscript>⚠ JavaScript is required for this application.</noscript>

      <div class="search-box" role="search">
        <input type="url" id="urlInput" placeholder="Paste a YouTube URL…"
               aria-label="YouTube URL" autocomplete="off" spellcheck="false">
        <button class="btn btn-primary" id="fetchBtn" type="button">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Get Info
        </button>
      </div>
      <div id="fetchError" class="hidden" style="max-width:680px;margin:10px auto 0"></div>
    </section>

    <!-- Server check banner -->
    <div id="serverBanner"></div>

    <!-- Video info + format selector (hidden until fetched) -->
    <div id="resultSection" class="hidden">

      <!-- Video card -->
      <div class="card" id="videoCard">
        <div class="card-body">
          <div class="video-info">
            <a id="thumbLink" href="#" target="_blank" rel="noopener noreferrer">
              <div class="video-thumb">
                <img id="thumbImg" src="" alt="Thumbnail" loading="lazy">
                <div class="thumb-play">
                  <svg width="36" height="36" viewBox="0 0 24 24" fill="white"><polygon points="5,3 19,12 5,21"/></svg>
                </div>
              </div>
            </a>
            <div class="video-meta">
              <h2 id="videoTitle">—</h2>
              <div class="meta-badges">
                <span class="badge badge-gray" id="metaDuration">—</span>
                <span class="badge badge-gray" id="metaViews"></span>
              </div>
              <div class="meta-uploader" id="metaUploader"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Format selector -->
      <div class="card" id="formatCard">
        <div class="card-header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
          Select Format &amp; Quality
        </div>
        <div class="card-body">

          <!-- Tabs -->
          <div class="tabs" role="tablist" aria-label="Download type">
            <button class="tab active" role="tab" aria-selected="true"  data-tab="smart">🎯 Smart</button>
            <button class="tab"        role="tab" aria-selected="false" data-tab="video">🎬 Video</button>
            <button class="tab"        role="tab" aria-selected="false" data-tab="audio">🎵 Audio</button>
            <button class="tab"        role="tab" aria-selected="false" data-tab="advanced">⚙ Advanced</button>
          </div>

          <!-- ── Smart tab ── -->
          <div class="tab-panel active" id="tab-smart">
            <div class="form-row">
              <div class="form-group">
                <label for="smartQuality">Quality preset</label>
                <select class="form-select" id="smartQuality">
                  <option value="bestvideo+bestaudio/best">Best available (video + audio)</option>
                  <option value="bestvideo[height<=2160]+bestaudio/best[height<=2160]">4K (2160p) max</option>
                  <option value="bestvideo[height<=1080]+bestaudio/best[height<=1080]">1080p max</option>
                  <option value="bestvideo[height<=720]+bestaudio/best[height<=720]">720p max</option>
                  <option value="bestvideo[height<=480]+bestaudio/best[height<=480]">480p max</option>
                  <option value="bestvideo[height<=360]+bestaudio/best[height<=360]">360p max</option>
                  <option value="worstvideo+worstaudio/worst">Smallest file</option>
                </select>
              </div>
              <div class="form-group">
                <label for="smartContainer">Container</label>
                <select class="form-select" id="smartContainer">
                  <option value="mp4">MP4 (recommended)</option>
                  <option value="webm">WebM</option>
                  <option value="mkv">MKV</option>
                  <option value="">Auto (no re-encode)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- ── Video tab ── -->
          <div class="tab-panel" id="tab-video">
            <div class="form-row">
              <div class="form-group">
                <label for="videoQuality">Video quality</label>
                <select class="form-select" id="videoQuality">
                  <option value="">Loading…</option>
                </select>
              </div>
              <div class="form-group">
                <label for="videoContainer">Container</label>
                <select class="form-select" id="videoContainer">
                  <option value="mp4">MP4</option>
                  <option value="webm">WebM</option>
                  <option value="mkv">MKV</option>
                  <option value="">Auto</option>
                </select>
              </div>
            </div>
          </div>

          <!-- ── Audio tab ── -->
          <div class="tab-panel" id="tab-audio">
            <div class="form-row">
              <div class="form-group">
                <label for="audioQuality">Audio quality</label>
                <select class="form-select" id="audioQuality">
                  <option value="bestaudio/best">Best quality</option>
                  <option value="bestaudio[abr<=320]/bestaudio">320 kbps max</option>
                  <option value="bestaudio[abr<=192]/bestaudio">192 kbps max</option>
                  <option value="bestaudio[abr<=128]/bestaudio">128 kbps max</option>
                  <option value="worstaudio">Smallest file</option>
                </select>
              </div>
              <div class="form-group">
                <label for="audioFormat">Audio format</label>
                <select class="form-select" id="audioFormat">
                  <option value="mp3">MP3</option>
                  <option value="m4a">M4A (AAC)</option>
                  <option value="ogg">OGG Vorbis</option>
                  <option value="opus">Opus</option>
                  <option value="flac">FLAC (lossless)</option>
                  <option value="wav">WAV (lossless)</option>
                  <option value="">Auto (source format)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- ── Advanced tab ── -->
          <div class="tab-panel" id="tab-advanced">
            <p style="font-size:.85rem;color:var(--text-muted);margin-bottom:12px">
              Pick an exact format from the list below. Formats marked
              <span class="fmt-type fmt-v">V</span> or
              <span class="fmt-type fmt-a">A</span> need merging (requires ffmpeg on the server).
            </p>
            <div class="format-table-wrap">
              <table class="format-table" id="formatTable" aria-label="Available formats">
                <thead>
                  <tr>
                    <th></th>
                    <th>ID</th>
                    <th>Type</th>
                    <th>Quality</th>
                    <th>Ext</th>
                    <th>Size</th>
                    <th>Codecs</th>
                  </tr>
                </thead>
                <tbody id="formatTableBody"></tbody>
              </table>
            </div>
            <div class="form-row mt-16">
              <div class="form-group">
                <label for="advContainer">Output container</label>
                <select class="form-select" id="advContainer">
                  <option value="">Keep source</option>
                  <option value="mp4">MP4</option>
                  <option value="webm">WebM</option>
                  <option value="mkv">MKV</option>
                  <option value="mp3">MP3 (audio)</option>
                  <option value="m4a">M4A (audio)</option>
                  <option value="ogg">OGG (audio)</option>
                  <option value="opus">Opus (audio)</option>
                  <option value="flac">FLAC (audio)</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Download action -->
          <div class="dl-action">
            <div class="dl-selected-info" id="dlInfo">—</div>
            <button class="btn btn-success" id="downloadBtn" type="button" disabled>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
              Download
            </button>
          </div>

          <!-- Progress -->
          <div class="progress-wrap" id="progressWrap">
            <div class="progress-label">
              <span id="progressLabel">Processing…</span>
              <span id="progressPct"></span>
            </div>
            <div class="progress-bar"><div class="progress-fill"></div></div>
          </div>

          <div id="dlError" class="hidden mt-8"></div>
        </div>
      </div>

    </div><!-- /#resultSection -->

    <!-- Playlist result -->
    <div id="playlistSection" class="hidden">
      <div class="card">
        <div class="card-header">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
          Playlist detected
        </div>
        <div class="card-body">
          <div class="alert alert-info">Playlist downloads are not supported in the web UI. Open individual video links from the list below to download them.</div>
          <div id="playlistEntries"></div>
        </div>
      </div>
    </div>

  </div><!-- /.container -->
</main>

<footer>
  <div class="container">
    Powered by <a href="https://github.com/druvx13/ytdl" target="_blank" rel="noopener">flatgreen/ytdl</a> &amp;
    <a href="https://github.com/yt-dlp/yt-dlp" target="_blank" rel="noopener">yt-dlp</a>.
    For personal use only — respect copyright law.
  </div>
</footer>

<!-- Hidden download form (submitted programmatically) -->
<form id="dlForm" method="POST" action="download.php" class="hidden">
  <input type="hidden" name="url"         id="dlUrl">
  <input type="hidden" name="format_id"   id="dlFormatId">
  <input type="hidden" name="output_type" id="dlOutputType">
  <input type="hidden" name="audio_only"  id="dlAudioOnly" value="0">
</form>

<script>
(function () {
  'use strict';

  // ── Theme ──────────────────────────────────────────────────────────────────
  const html       = document.documentElement;
  const themeBtn   = document.getElementById('themeToggle');
  const savedTheme = localStorage.getItem('theme') || 'dark';
  setTheme(savedTheme);

  themeBtn.addEventListener('click', () => {
    setTheme(html.dataset.theme === 'dark' ? 'light' : 'dark');
  });

  function setTheme(t) {
    html.dataset.theme = t;
    themeBtn.textContent = t === 'dark' ? '☀️' : '🌙';
    localStorage.setItem('theme', t);
  }

  // ── Server availability check ──────────────────────────────────────────────
  const banner = document.getElementById('serverBanner');
  fetch('api.php?action=check')
    .then(r => r.json())
    .then(d => {
      if (!d.available) {
        banner.innerHTML = `<div class="container"><div class="alert alert-error">
          <strong>⚠ yt-dlp not found:</strong> Neither <code>yt-dlp</code> nor <code>youtube-dl</code>
          is installed on this server. Please install
          <a href="https://github.com/yt-dlp/yt-dlp#installation" target="_blank" rel="noopener">yt-dlp</a>
          and make sure it is in your <code>PATH</code>.
        </div></div>`;
      }
    })
    .catch(() => {/* silent */});

  // ── State ──────────────────────────────────────────────────────────────────
  let currentFormats = [];   // all formats from API
  let selectedFormatId = ''; // for advanced tab
  let currentUrl = '';
  let currentTab = 'smart';

  // ── DOM refs ───────────────────────────────────────────────────────────────
  const urlInput       = document.getElementById('urlInput');
  const fetchBtn       = document.getElementById('fetchBtn');
  const fetchError     = document.getElementById('fetchError');
  const resultSection  = document.getElementById('resultSection');
  const playlistSection= document.getElementById('playlistSection');
  const downloadBtn    = document.getElementById('downloadBtn');
  const dlInfo         = document.getElementById('dlInfo');
  const progressWrap   = document.getElementById('progressWrap');
  const progressLabel  = document.getElementById('progressLabel');
  const dlError        = document.getElementById('dlError');

  const smartQuality   = document.getElementById('smartQuality');
  const smartContainer = document.getElementById('smartContainer');
  const videoQuality   = document.getElementById('videoQuality');
  const videoContainer = document.getElementById('videoContainer');
  const audioQuality   = document.getElementById('audioQuality');
  const audioFormat    = document.getElementById('audioFormat');
  const advContainer   = document.getElementById('advContainer');
  const formatTableBody= document.getElementById('formatTableBody');

  // ── Tabs ───────────────────────────────────────────────────────────────────
  document.querySelectorAll('.tab').forEach(tab => {
    tab.addEventListener('click', () => {
      document.querySelectorAll('.tab').forEach(t => { t.classList.remove('active'); t.setAttribute('aria-selected','false'); });
      document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
      tab.classList.add('active');
      tab.setAttribute('aria-selected','true');
      currentTab = tab.dataset.tab;
      const panel = document.getElementById('tab-' + currentTab);
      if (panel) panel.classList.add('active');
      updateDlInfo();
    });
  });

  // ── Fetch video info ───────────────────────────────────────────────────────
  fetchBtn.addEventListener('click', doFetch);
  urlInput.addEventListener('keydown', e => { if (e.key === 'Enter') doFetch(); });

  // Paste support
  urlInput.addEventListener('paste', () => {
    setTimeout(() => {
      if (urlInput.value.trim()) doFetch();
    }, 100);
  });

  function doFetch() {
    const url = urlInput.value.trim();
    if (!url) { showFetchError('Please enter a YouTube URL.'); return; }

    setFetchLoading(true);
    clearFetchError();
    resultSection.classList.add('hidden');
    playlistSection.classList.add('hidden');

    fetch('api.php?action=info&url=' + encodeURIComponent(url))
      .then(r => r.json())
      .then(data => {
        setFetchLoading(false);
        if (data.error) { showFetchError(data.error); return; }
        currentUrl = data.webpage_url || url;

        if (data.is_playlist) {
          renderPlaylist(data);
        } else {
          currentFormats = data.formats || [];
          renderVideoCard(data);
          renderFormats(currentFormats);
          resultSection.classList.remove('hidden');
          downloadBtn.disabled = false;
          updateDlInfo();
        }
      })
      .catch(err => {
        setFetchLoading(false);
        showFetchError('Network error: ' + err.message);
      });
  }

  function setFetchLoading(loading) {
    if (loading) {
      fetchBtn.disabled = true;
      fetchBtn.innerHTML = '<span class="spinner"></span> Fetching…';
    } else {
      fetchBtn.disabled = false;
      fetchBtn.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg> Get Info';
    }
  }

  function showFetchError(msg) {
    fetchError.innerHTML = `<div class="alert alert-error">❌ ${escHtml(msg)}</div>`;
    fetchError.classList.remove('hidden');
  }

  function clearFetchError() {
    fetchError.innerHTML = '';
    fetchError.classList.add('hidden');
  }

  // ── Render video card ──────────────────────────────────────────────────────
  function renderVideoCard(data) {
    document.getElementById('videoTitle').textContent = data.title || '—';
    document.getElementById('metaDuration').textContent = '⏱ ' + (data.duration_hr || '—');
    document.getElementById('metaUploader').textContent = data.uploader ? '📺 ' + data.uploader : '';

    const views = data.view_count;
    const viewBadge = document.getElementById('metaViews');
    viewBadge.textContent = views ? '👁 ' + formatNumber(views) : '';

    const thumb = data.thumbnail || '';
    const thumbImg  = document.getElementById('thumbImg');
    const thumbLink = document.getElementById('thumbLink');
    thumbImg.src     = thumb;
    thumbImg.alt     = data.title || '';
    thumbLink.href   = data.webpage_url || '#';
  }

  // ── Render format lists ────────────────────────────────────────────────────
  function renderFormats(formats) {
    // ── Video quality dropdown (Video tab) ──
    const videoOnlyOrCombined = formats.filter(f => f.type === 'video+audio' || f.type === 'video');
    videoQuality.innerHTML = '';

    // Add "Best" option
    let opt = document.createElement('option');
    opt.value = 'bestvideo+bestaudio/best';
    opt.textContent = 'Best available';
    videoQuality.appendChild(opt);

    // Group by resolution
    const seenRes = new Set();
    videoOnlyOrCombined.forEach(f => {
      const res = f.resolution || f.height + 'p' || 'unknown';
      if (!seenRes.has(res)) {
        seenRes.add(res);
        const o = document.createElement('option');
        const fmtIdForRes = buildBestFormatId(f.height);
        o.value = fmtIdForRes;
        o.textContent = res + (f.fps && f.fps > 30 ? ' @' + f.fps + 'fps' : '')
                      + (f.filesize_hr && f.filesize_hr !== 'unknown' ? ' (~' + f.filesize_hr + ')' : '');
        videoQuality.appendChild(o);
      }
    });

    // ── Advanced table ──
    formatTableBody.innerHTML = '';
    formats.forEach((f, idx) => {
      const tr = document.createElement('tr');
      tr.classList.add('selectable');
      tr.dataset.formatId = f.format_id;
      tr.innerHTML = `
        <td><input type="radio" name="advFmt" value="${escHtml(f.format_id)}" id="fmt_${idx}"></td>
        <td><code>${escHtml(f.format_id)}</code></td>
        <td><span class="fmt-type ${f.type==='video+audio'?'fmt-va':f.type==='video'?'fmt-v':'fmt-a'}">${escHtml(typeLabel(f.type))}</span></td>
        <td>${escHtml(f.resolution || '—')}</td>
        <td>${escHtml(f.ext || '—')}</td>
        <td style="white-space:nowrap">${escHtml(f.filesize_hr || '—')}</td>
        <td style="font-size:.78rem;color:var(--text-muted)">${escHtml(truncCodec(f.vcodec))}/${escHtml(truncCodec(f.acodec))}</td>`;

      tr.addEventListener('click', () => selectAdvancedRow(tr, f.format_id));
      formatTableBody.appendChild(tr);
    });

    // Select first row by default
    const firstRow = formatTableBody.querySelector('tr.selectable');
    if (firstRow) {
      const firstId = firstRow.dataset.formatId;
      selectAdvancedRow(firstRow, firstId);
    }
  }

  function buildBestFormatId(height) {
    if (!height) return 'bestvideo+bestaudio/best';
    return `bestvideo[height<=${height}]+bestaudio/best[height<=${height}]`;
  }

  function selectAdvancedRow(tr, formatId) {
    document.querySelectorAll('#formatTableBody tr').forEach(r => r.classList.remove('selected'));
    tr.classList.add('selected');
    const radio = tr.querySelector('input[type=radio]');
    if (radio) radio.checked = true;
    selectedFormatId = formatId;
    updateDlInfo();
  }

  function typeLabel(type) {
    return { 'video+audio': 'V+A', 'video': 'V', 'audio': 'A' }[type] || type;
  }

  function truncCodec(c) {
    if (!c || c === 'none') return 'none';
    return c.split('.')[0].substring(0, 8);
  }

  // ── Update download info summary ───────────────────────────────────────────
  smartQuality.addEventListener('change',   updateDlInfo);
  smartContainer.addEventListener('change', updateDlInfo);
  videoQuality.addEventListener('change',   updateDlInfo);
  videoContainer.addEventListener('change', updateDlInfo);
  audioQuality.addEventListener('change',   updateDlInfo);
  audioFormat.addEventListener('change',    updateDlInfo);
  advContainer.addEventListener('change',   updateDlInfo);

  function updateDlInfo() {
    const { fmtId, outType, isAudio, label } = buildDownloadParams();
    dlInfo.innerHTML = label;
  }

  function buildDownloadParams() {
    let fmtId = '', outType = '', isAudio = false, label = '';

    if (currentTab === 'smart') {
      fmtId   = smartQuality.value;
      outType = smartContainer.value;
      label   = `<strong>${smartQuality.options[smartQuality.selectedIndex].text}</strong>`
              + (outType ? ` → <strong>.${outType}</strong>` : ' (auto container)');
    } else if (currentTab === 'video') {
      fmtId   = videoQuality.value;
      outType = videoContainer.value;
      label   = `<strong>${videoQuality.options[videoQuality.selectedIndex]?.text || '—'}</strong>`
              + (outType ? ` → <strong>.${outType}</strong>` : ' (auto container)');
    } else if (currentTab === 'audio') {
      fmtId   = audioQuality.value;
      outType = audioFormat.value;
      isAudio = true;
      label   = `Audio: <strong>${audioQuality.options[audioQuality.selectedIndex].text}</strong>`
              + (outType ? ` → <strong>.${outType}</strong>` : ' (auto format)');
    } else if (currentTab === 'advanced') {
      fmtId   = selectedFormatId || 'bestvideo+bestaudio/best';
      outType = advContainer.value;
      // detect audio-only output type
      const audioTypes = ['mp3','m4a','ogg','opus','flac','wav'];
      isAudio = audioTypes.includes(outType);
      label   = `Format ID: <strong>${escHtml(fmtId)}</strong>`
              + (outType ? ` → <strong>.${outType}</strong>` : ' (keep source)');
    }

    return { fmtId, outType, isAudio, label };
  }

  // ── Download ───────────────────────────────────────────────────────────────
  downloadBtn.addEventListener('click', doDownload);

  function doDownload() {
    const { fmtId, outType, isAudio } = buildDownloadParams();
    if (!currentUrl || !fmtId) return;

    // Fill the hidden form
    document.getElementById('dlUrl').value        = currentUrl;
    document.getElementById('dlFormatId').value   = fmtId;
    document.getElementById('dlOutputType').value = outType;
    document.getElementById('dlAudioOnly').value  = isAudio ? '1' : '0';

    // Show progress
    setDownloadLoading(true);

    // Submit form — browser handles the file download dialog.
    // After a few seconds re-enable the button (we can't detect when the
    // download stream ends from an iframe-less form submit).
    document.getElementById('dlForm').submit();

    // Re-enable after a reasonable timeout
    setTimeout(() => setDownloadLoading(false), 20000);
  }

  function setDownloadLoading(loading) {
    downloadBtn.disabled = loading;
    progressWrap.classList.toggle('visible', loading);
    dlError.classList.add('hidden');
    if (loading) {
      downloadBtn.innerHTML = '<span class="spinner"></span> Downloading…';
      progressLabel.textContent = 'Preparing download on server…';
    } else {
      downloadBtn.innerHTML = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Download`;
    }
  }

  // ── Playlist ───────────────────────────────────────────────────────────────
  function renderPlaylist(data) {
    const entries = data.entries || [];
    let html = `<p style="color:var(--text-muted);font-size:.9rem;margin-bottom:12px">${entries.length} video(s) found.</p>`;
    html += '<ul style="list-style:none;display:flex;flex-direction:column;gap:8px">';
    entries.forEach(e => {
      html += `<li style="display:flex;align-items:center;gap:10px;padding:8px;background:var(--surface2);border-radius:var(--radius-sm)">
        ${e.thumbnail ? `<img src="${escHtml(e.thumbnail)}" alt="" style="width:60px;height:34px;object-fit:cover;border-radius:4px;flex-shrink:0">` : ''}
        <div style="flex:1;min-width:0">
          <div style="font-size:.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${escHtml(e.title || '—')}</div>
          <div style="font-size:.78rem;color:var(--text-muted)">${escHtml(e.duration_hr || '')}</div>
        </div>
        ${e.webpage_url ? `<a href="${escHtml(e.webpage_url)}" target="_blank" rel="noopener" class="btn btn-secondary" style="flex-shrink:0;padding:5px 12px;font-size:.8rem">Open</a>` : ''}
      </li>`;
    });
    html += '</ul>';
    document.getElementById('playlistEntries').innerHTML = html;
    playlistSection.classList.remove('hidden');
  }

  // ── Utilities ──────────────────────────────────────────────────────────────
  function escHtml(str) {
    if (str == null) return '';
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }

  function formatNumber(n) {
    if (n >= 1e9) return (n/1e9).toFixed(1) + 'B';
    if (n >= 1e6) return (n/1e6).toFixed(1) + 'M';
    if (n >= 1e3) return (n/1e3).toFixed(1) + 'K';
    return String(n);
  }

})();
</script>
</body>
</html>
