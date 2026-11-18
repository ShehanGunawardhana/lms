@props([
    'purpose' => 'general',
    'allowedTypes' => 'mp4,mov,webm,pdf,jpg,png,zip',
    'maxSizeMb' => 50,
    'label' => __('Upload File (Resumable)'),
    'helper' => __('Drag and drop or tap to choose a file up to 50 MB'),
])

<div 
    x-data="{
        resumable: null,
        isUploading: false,
        isPaused: false,
        progress: 0,
        fileName: '',
        fileSizeFormatted: '',
        errorMessage: '',
        successData: null,
        initResumable() {
            if (typeof Resumable === 'undefined') {
                return;
            }

            this.resumable = new Resumable({
                target: '{{ route('files.chunk-upload') }}',
                query: {
                    _token: '{{ csrf_token() }}',
                    purpose: '{{ $purpose }}',
                    allowed_types: '{{ $allowedTypes }}'
                },
                fileType: '{{ $allowedTypes }}'.split(','),
                chunkSize: 2 * 1024 * 1024, // 2 MB Chunks
                maxFiles: 1,
                maxFileSize: {{ $maxSizeMb }} * 1024 * 1024,
                testChunks: false,
                simultaneousUploads: 2,
                maxChunkRetries: 3,
                chunkRetryInterval: 1000,
            });

            this.resumable.assignBrowse(this.$refs.browseBtn);
            this.resumable.assignDrop(this.$refs.dropZone);

            this.resumable.on('fileAdded', (file) => {
                this.fileName = file.fileName;
                this.fileSizeFormatted = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                this.errorMessage = '';
                this.successData = null;
                this.isUploading = true;
                this.progress = 0;
                this.resumable.upload();
            });

            this.resumable.on('fileProgress', (file) => {
                this.progress = Math.floor(file.progress() * 100);
            });

            this.resumable.on('fileSuccess', (file, response) => {
                this.isUploading = false;
                try {
                    const data = JSON.parse(response);
                    if (data.status === 'success') {
                        this.successData = data;
                        this.$dispatch('file-uploaded', data);
                    } else {
                        this.errorMessage = data.message || '{{ __('Upload failed.') }}';
                    }
                } catch (e) {
                    this.errorMessage = '{{ __('Invalid server response.') }}';
                }
            });

            this.resumable.on('fileError', (file, message) => {
                this.isUploading = false;
                try {
                    const err = JSON.parse(message);
                    this.errorMessage = err.message || '{{ __('Upload failed. Please retry.') }}';
                } catch (e) {
                    this.errorMessage = message || '{{ __('Connection lost. Tap retry.') }}';
                }
            });
        },
        retry() {
            if (this.resumable) {
                this.errorMessage = '';
                this.isUploading = true;
                this.resumable.retry();
            }
        },
        cancel() {
            if (this.resumable) {
                this.resumable.cancel();
                this.isUploading = false;
                this.progress = 0;
                this.fileName = '';
            }
        }
    }"
    x-init="initResumable()"
    class="w-full space-y-4 select-none"
>
    <!-- Drop & Browse Area -->
    <div 
        x-ref="dropZone"
        class="relative border-2 border-dashed border-slate-300 hover:border-brand-500 rounded-2xl p-6 sm:p-8 text-center bg-slate-50/50 hover:bg-white transition-all cursor-pointer group"
    >
        <div class="flex flex-col items-center">
            <div class="w-12 h-12 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform shadow-xs">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
            </div>
            <h4 class="text-sm font-bold text-slate-800 tracking-tight">{{ $label }}</h4>
            <p class="text-xs text-slate-500 mt-1">{{ $helper }}</p>

            <button 
                type="button" 
                x-ref="browseBtn"
                class="mt-4 inline-flex items-center px-4 py-2 rounded-xl text-xs font-semibold text-white bg-brand-600 hover:bg-brand-700 shadow-sm shadow-brand-500/25 transition-all"
            >
                {{ __('Select File') }}
            </button>
        </div>
    </div>

    <!-- Active Upload Progress Card -->
    <div x-show="isUploading" x-cloak class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-md space-y-3">
        <div class="flex items-center justify-between text-xs font-semibold">
            <span class="text-slate-800 truncate max-w-[200px]" x-text="fileName"></span>
            <span class="text-brand-600 font-mono font-bold" x-text="progress + '%'"></span>
        </div>

        <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden">
            <div class="bg-brand-600 h-2.5 rounded-full transition-all duration-150" :style="'width: ' + progress + '%'"></div>
        </div>

        <div class="flex items-center justify-between text-xs">
            <span class="text-slate-400" x-text="fileSizeFormatted"></span>
            <button type="button" @click="cancel()" class="text-rose-600 hover:underline font-semibold">
                {{ __('Cancel') }}
            </button>
        </div>
    </div>

    <!-- Success Feedback -->
    <div x-show="successData" x-cloak class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center space-x-3">
        <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <div class="flex-1 text-xs">
            <span class="font-bold">{{ __('Upload complete!') }}</span>
            <span class="block text-emerald-600" x-text="successData?.original_name"></span>
        </div>
    </div>

    <!-- Error & Retry Banner -->
    <div x-show="errorMessage" x-cloak class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between">
        <div class="flex items-center space-x-2 text-xs">
            <svg class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span x-text="errorMessage"></span>
        </div>
        <button type="button" @click="retry()" class="text-xs font-bold text-rose-700 underline hover:text-rose-900 ml-3">
            {{ __('Retry') }}
        </button>
    </div>
</div>
