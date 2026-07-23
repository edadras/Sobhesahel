<x-filament-panels::page>
    @php($disks = $this->getDisks())
    @php($listing = $this->getListing())

    <div class="space-y-4">
        <x-filament::section>
            <div class="flex flex-wrap items-center justify-between gap-3">
                {{-- Disk switcher --}}
                <div class="flex flex-wrap items-center gap-2">
                    @foreach ($disks as $diskName => $diskLabel)
                        <x-filament::button
                            size="sm"
                            :color="$this->disk === $diskName ? 'primary' : 'gray'"
                            :outlined="$this->disk !== $diskName"
                            icon="heroicon-o-circle-stack"
                            wire:click="setDisk(@js($diskName))"
                        >
                            {{ $diskLabel }}
                        </x-filament::button>
                    @endforeach
                </div>

                {{-- Search --}}
                <div class="w-full sm:w-72">
                    <x-filament::input.wrapper prefix-icon="heroicon-o-magnifying-glass">
                        <x-filament::input
                            type="search"
                            wire:model.live.debounce.500ms="search"
                            placeholder="جستجوی نام فایل در این شاخه..."
                        />
                    </x-filament::input.wrapper>
                </div>
            </div>

            {{-- Breadcrumbs --}}
            <nav class="mt-4 flex flex-wrap items-center gap-1 text-sm" aria-label="مسیر پوشه">
                <button
                    type="button"
                    wire:click="openFolder('')"
                    class="inline-flex items-center gap-1 font-medium text-primary-600 hover:underline dark:text-primary-400"
                >
                    <x-filament::icon icon="heroicon-o-home" class="h-4 w-4" />
                    ریشه
                </button>

                @php($crumb = '')
                @foreach (array_filter(explode('/', $this->path)) as $segment)
                    @php($crumb = ltrim($crumb.'/'.$segment, '/'))
                    <span class="text-gray-400 dark:text-gray-500">/</span>
                    <button
                        type="button"
                        wire:click="openFolder(@js($crumb))"
                        class="font-medium text-primary-600 hover:underline dark:text-primary-400"
                    >
                        {{ $segment }}
                    </button>
                @endforeach

                @if (trim($this->search) !== '')
                    <span class="mr-2 text-xs text-gray-500 dark:text-gray-400">
                        (نتایج جستجو برای «{{ $this->search }}» — {{ $listing['total'] }} فایل)
                    </span>
                @endif
            </nav>
        </x-filament::section>

        @if (! empty($listing['error']))
            <x-filament::section>
                <p class="text-sm text-danger-600 dark:text-danger-400">{{ $listing['error'] }}</p>
            </x-filament::section>
        @endif

        {{-- Folders --}}
        @if (! $listing['searching'] && count($listing['folders']) > 0)
            <x-filament::section>
                <x-slot name="heading">پوشه‌ها ({{ count($listing['folders']) }})</x-slot>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6">
                    @foreach ($listing['folders'] as $folder)
                        <button
                            type="button"
                            wire:click="openFolder(@js($folder['path']))"
                            class="flex items-center gap-2 rounded-lg border border-gray-200 p-3 text-right transition hover:bg-gray-50 dark:border-white/10 dark:hover:bg-white/5"
                            title="{{ $folder['name'] }}"
                        >
                            <x-filament::icon icon="heroicon-s-folder" class="h-6 w-6 shrink-0 text-warning-500" />
                            <span class="truncate text-sm">{{ $folder['name'] }}</span>
                        </button>
                    @endforeach
                </div>
            </x-filament::section>
        @endif

        {{-- Files --}}
        <x-filament::section>
            <x-slot name="heading">
                فایل‌ها ({{ $listing['total'] }})
            </x-slot>

            @if (count($listing['files']) === 0)
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if ($listing['searching'])
                        فایلی مطابق جستجوی شما یافت نشد.
                    @else
                        این پوشه فایلی ندارد. با دکمه «آپلود فایل» فایل جدید اضافه کنید.
                    @endif
                </p>
            @else
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-6">
                    @foreach ($listing['files'] as $file)
                        <div
                            class="flex flex-col overflow-hidden rounded-lg border border-gray-200 dark:border-white/10"
                            wire:key="file-{{ md5($this->disk.'/'.$file['path']) }}"
                        >
                            {{-- Thumbnail / icon --}}
                            <div class="flex h-28 items-center justify-center bg-gray-50 dark:bg-white/5">
                                @if ($file['type'] === 'image')
                                    <img
                                        src="{{ $file['url'] }}"
                                        alt="{{ $file['name'] }}"
                                        loading="lazy"
                                        class="h-full w-full object-cover"
                                    />
                                @else
                                    <x-filament::icon
                                        :icon="match ($file['type']) {
                                            'audio' => 'heroicon-o-musical-note',
                                            'video' => 'heroicon-o-film',
                                            default => 'heroicon-o-document',
                                        }"
                                        class="h-10 w-10 text-gray-400 dark:text-gray-500"
                                    />
                                @endif
                            </div>

                            {{-- Meta --}}
                            <div class="flex flex-1 flex-col gap-1 p-2">
                                <p class="truncate text-xs font-medium" title="{{ $file['path'] }}" dir="ltr">
                                    {{ $file['name'] }}
                                </p>
                                <p class="text-[11px] text-gray-500 dark:text-gray-400">
                                    {{ $file['size_human'] }}
                                    <span class="mx-1">·</span>
                                    <span dir="ltr">{{ $file['modified_jalali'] }}</span>
                                </p>
                                @if ($listing['searching'] && $file['directory'] !== '')
                                    <button
                                        type="button"
                                        wire:click="openFolder(@js($file['directory']))"
                                        class="truncate text-right text-[11px] text-primary-600 hover:underline dark:text-primary-400"
                                        dir="ltr"
                                        title="{{ $file['directory'] }}"
                                    >
                                        {{ $file['directory'] }}
                                    </button>
                                @endif

                                {{-- Actions --}}
                                <div class="mt-auto flex flex-wrap items-center justify-center gap-0.5 pt-1">
                                    @if (in_array($file['type'], ['image', 'audio', 'video'], true))
                                        {{ ($this->previewFileAction)(['path' => $file['path']]) }}
                                    @endif

                                    <x-filament::icon-button
                                        icon="heroicon-o-clipboard-document"
                                        color="gray"
                                        label="کپی نشانی"
                                        x-data
                                        x-on:click="
                                            navigator.clipboard.writeText(@js($file['url']))
                                                .then(() => new FilamentNotification().title('نشانی فایل کپی شد').success().send())
                                                .catch(() => window.prompt('نشانی فایل:', @js($file['url'])))
                                        "
                                    />

                                    <x-filament::icon-button
                                        icon="heroicon-o-arrow-down-tray"
                                        color="gray"
                                        label="دانلود"
                                        wire:click="download(@js($file['path']))"
                                    />

                                    {{ ($this->renameFileAction)(['path' => $file['path']]) }}
                                    {{ ($this->moveFileAction)(['path' => $file['path']]) }}
                                    {{ ($this->deleteFileAction)(['path' => $file['path']]) }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Pagination --}}
                @if ($listing['pages'] > 1)
                    <div class="mt-4 flex items-center justify-center gap-3">
                        <x-filament::button
                            size="sm"
                            color="gray"
                            icon="heroicon-o-chevron-right"
                            :disabled="$listing['page'] <= 1"
                            wire:click="setFilesPage({{ $listing['page'] - 1 }})"
                        >
                            قبلی
                        </x-filament::button>

                        <span class="text-sm text-gray-600 dark:text-gray-300">
                            صفحه {{ $listing['page'] }} از {{ $listing['pages'] }}
                        </span>

                        <x-filament::button
                            size="sm"
                            color="gray"
                            icon="heroicon-o-chevron-left"
                            icon-position="after"
                            :disabled="$listing['page'] >= $listing['pages']"
                            wire:click="setFilesPage({{ $listing['page'] + 1 }})"
                        >
                            بعدی
                        </x-filament::button>
                    </div>
                @endif
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
