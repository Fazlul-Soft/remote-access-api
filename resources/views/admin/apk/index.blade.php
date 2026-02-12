@extends('admin.layout')
@section('title', 'App Versions')

@section('content')
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Managed App Versions</h3>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload New APK
            </button>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Version</th>
                                <th>Platform</th>
                                <th>Status</th>
                                <th>Release Notes</th>
                                <th>Upload Date</th>
                                <th>Download / Share Link</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($versions as $version)
                                <tr>
                                    <td><strong>{{ $version->version_name }}</strong></td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($version->platform) }}</span></td>
                                    <td>
                                        <form action="{{ route('admin.app-versions.toggle-active', $version->id) }}"
                                            method="POST">
                                            @csrf
                                            <button type="submit"
                                                class="btn btn-sm {{ $version->is_active ? 'btn-success' : 'btn-danger' }}">
                                                {{ $version->is_active ? 'Active' : 'Inactive' }}
                                            </button>
                                        </form>
                                    </td>
                                    <td><small class="text-muted">{{ Str::limit($version->release_notes, 30) }}</small></td>
                                    <td>{{ $version->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <div class="btn-group">
                                                <a href="{{ asset($version->file_path) }}"
                                                    class="btn btn-sm btn-outline-primary" download title="Download">
                                                    <i class="fas fa-download"></i> Download
                                                </a>
                                                <button class="btn btn-sm btn-outline-dark"
                                                    onclick="copyToClipboard('{{ asset($version->file_path) }}')"
                                                    title="Copy Link">
                                                    <i class="fas fa-copy"></i> Copy Link
                                                </button>
                                            </div>

                                            <form action="{{ route('admin.app-versions.delete', $version->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure? This will permanently delete the APK file.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4">No versions uploaded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="uploadModal" tabindex="-1" aria-labelledby="uploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="uploadModalLabel text-white">Upload New App Version</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.app-versions.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Version Name</label>
                                <input type="text" name="version_name" class="form-control" placeholder="e.g. 1.0.5"
                                    required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Platform</label>
                                <select name="platform" class="form-select" required>
                                    <option value="android">Android (APK)</option>
                                    <option value="ios">iOS</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Release Notes</label>
                            <textarea name="release_notes" class="form-control" rows="3" placeholder="What's new in this version?"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Select APK File</label>
                            <input type="file" name="apk_file" class="form-control" accept=".apk" required>
                            <div class="form-text">Stored in <code>/public/apk_versions/</code></div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_active" id="isActive" checked>
                            <label class="form-check-label" for="isActive">Activate this version immediately</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Start Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Download link copied to clipboard!');
            });
        }

        // Automatically open modal if there are validation errors
        @if ($errors->any())
            var myModal = new bootstrap.Modal(document.getElementById('uploadModal'));
            myModal.show();
        @endif
    </script>
@endsection
