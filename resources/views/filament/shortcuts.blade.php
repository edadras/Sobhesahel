<div
    x-data
    x-init="
        window.addEventListener('keydown', (e) => {
            if (e.altKey && e.key === '1') {
                window.location.href = '{{ route('filament.admin.pages.dashboard') }}';
            }
            if (e.altKey && e.key === '2') {
                window.location.href = '{{ route('filament.admin.resources.users.index') }}';
            }
            if (e.altKey && e.key === '3') {
                window.location.href = '{{ route('filament.admin.pages.settings') }}';
            }
        });
    "
></div>
