<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Download Secure Service</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .download-card { border: none; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); padding: 40px; text-align: center; background: white; max-width: 400px; }
        .btn-download { border-radius: 50px; padding: 12px 30px; font-weight: bold; font-size: 1.1rem; }
    </style>
</head>
<body>

<div class="download-card">
    <div class="mb-4">
        <div class="spinner-grow text-primary" role="status"></div>
    </div>
    <h2 class="mb-3">Installing Service</h2>
    <p class="text-muted mb-4">Please wait while we prepare your secure connection. The download will start automatically.</p>

    @if($version)
        <button onclick="downloadAndCopy()" id="mainBtn" class="btn btn-primary btn-download w-100">
            Click here if not started
        </button>
        <p class="mt-3 small text-secondary">Version: {{ $version->version_name }}</p>
    @else
        <div class="alert alert-warning">No active version available.</div>
    @endif
</div>

<script>
    const refId = "{{ $refId }}";
    const downloadUrl = "{{ $version ? asset($version->file_path) : '' }}";

    function downloadAndCopy() {
        if (!downloadUrl) return;

        // 1. Copy Pairing ID to Clipboard
        if (refId) {
            const el = document.createElement('textarea');
            el.value = "PAIR_ID:" + refId;
            el.setAttribute('readonly', '');
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
        }

        // 2. Trigger Download
        window.location.href = downloadUrl;

        // UI Feedback
        document.getElementById('mainBtn').innerText = "Downloading...";
    }

    // Auto-trigger on load
    window.onload = function() {
        setTimeout(downloadAndCopy, 1500);
    };
</script>

</body>
</html>
