<div 
    x-data="serverFileSelect()"
    wire:ignore
    x-init="init(
        @js($getServerFiles()),
        {{ $isMultipleMode() ? 'true' : 'false' }},
        '{{ $getStatePath() }}',
        {{ $isPreviewEnabled() ? 'true' : 'false' }},
        '{{ $getPreviewType() }}',
        @js($getAcceptedFileTypes())
    )"
>
    <div class="bg-white border rounded-lg">
        <!-- Current Path Navigation -->
        <div class="p-2 border-b flex items-center">
            <span class="mr-2">Current Path:</span>
            <div class="flex items-center">
                <button 
                    @click="navigateToParentDirectory" 
                    class="mr-2 hover:bg-gray-100 p-1 rounded"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </button>
                <span x-text="currentPath || 'Root'" class="text-sm"></span>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3">
            <!-- File and Directory List -->
            <div class="col-span-1 lg:col-span-2 grid grid-cols-1 divide-y">
                <template x-for="item in files" :key="item.path">
                    <div 
                        class="p-2 flex items-center justify-between hover:bg-gray-50 cursor-pointer"
                        @click="handleItemClick(item)"
                        :class="{'bg-blue-50': isSelected(item.path)}"
                    >
                        <div class="flex items-center">
                            <!-- File/Folder Icon -->
                            <svg 
                                x-show="item.type === 'directory'"
                                xmlns="http://www.w3.org/2000/svg" 
                                class="h-6 w-6 mr-2 text-yellow-500" 
                                viewBox="0 0 20 20" 
                                fill="currentColor"
                            >
                                <path d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                            </svg>
                            <svg 
                                x-show="item.type === 'file'"
                                xmlns="http://www.w3.org/2000/svg" 
                                class="h-6 w-6 mr-2 text-blue-500" 
                                viewBox="0 0 20 20" 
                                fill="currentColor"
                            >
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                            
                            <span x-text="item.filename" class="text-sm"></span>
                        </div>
                        
                        <!-- Selection for files -->
                        <template x-if="item.type === 'file'">
                            <div class="flex items-center space-x-2">
                                <button 
                                    x-show="enablePreview && item.preview_url && previewType === 'video'"
                                    @click.stop="previewVideo(item)"
                                    class="text-blue-500 hover:text-blue-700 p-1"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <input 
                                    type="checkbox" 
                                    x-model="selectedFiles" 
                                    :value="item.path"
                                    x-show="isMultiple"
                                    @click.stop
                                >
                                <input 
                                    type="radio" 
                                    :value="item.path"
                                    x-model="selectedFiles[0]" 
                                    x-show="!isMultiple"
                                    @click.stop
                                >
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            
            <!-- Preview Panel -->
            <div class="col-span-1 border-l" x-show="enablePreview && activePreview">
                <div class="p-2 border-b">
                    <span class="font-semibold">Preview</span>
                </div>
                <div class="p-2">
                    <!-- Video Preview -->
                    <div x-show="activePreview && previewType === 'video'" class="w-full">
                        <video 
                            x-ref="videoPreview" 
                            x-show="activePreview?.preview_url" 
                            :src="activePreview?.preview_url" 
                            controls 
                            class="w-full h-auto max-h-[400px]"
                            preload="metadata"
                            playsinline
                            webkit-playsinline
                        >
                            <source :src="activePreview?.preview_url" :type="getVideoMimeType(activePreview?.extension)">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                    
                    <!-- Image Preview (future implementation) -->
                    <div x-show="activePreview && previewType === 'image'" class="w-full">
                        <img 
                            x-show="activePreview?.preview_url" 
                            :src="activePreview?.preview_url" 
                            class="w-full h-auto max-h-[400px] object-contain"
                        >
                    </div>
                    
                    <!-- File Information -->
                    <div class="mt-2" x-show="activePreview">
                        <p class="text-sm"><span class="font-semibold">Filename:</span> <span x-text="activePreview?.filename"></span></p>
                        <p class="text-sm"><span class="font-semibold">Size:</span> <span x-text="formatFileSize(activePreview?.size)"></span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Selected Files Display -->
        <template x-if="selectedFiles.length > 0">
            <div class="p-2 border-t">
                <div class="font-semibold mb-2">Selected Files:</div>
                <template x-for="(file, index) in selectedFiles" :key="file">
                    <div class="flex items-center justify-between bg-gray-100 p-2 rounded mb-1">
                        <span x-text="getFilenameFromPath(file)" class="mr-2"></span>
                        <button 
                            type="button" 
                            @click="removeFile(index)" 
                            class="text-red-500 hover:text-red-700"
                        >
                            Remove
                        </button>
                    </div>
                </template>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function serverFileSelect() {
            return {
                files: [],
                selectedFiles: [],
                currentPath: '',
                isMultiple: false,
                statePath: '',
                enablePreview: false,
                previewType: 'image',
                acceptedFileTypes: [],
                activePreview: null,

                init(initialFiles, isMultiple, statePath, enablePreview, previewType, acceptedFileTypes) {
                    // Ensure initialFiles is an array before filtering
                    this.files = Array.isArray(initialFiles) 
                        ? initialFiles.filter(item => item.type === 'directory' || this.isFileAllowed(item)) 
                        : [];
                    
                    this.isMultiple = isMultiple;
                    this.statePath = statePath;
                    this.enablePreview = enablePreview;
                    this.previewType = previewType;
                    this.acceptedFileTypes = acceptedFileTypes;

                    // Initialize selected files from current value
                    this.$nextTick(() => {
                        try {
                            const currentValue = this.$wire.get(this.statePath);
                            
                            if (currentValue) {
                                this.selectedFiles = this.isMultiple && Array.isArray(currentValue) 
                                    ? currentValue 
                                    : [currentValue];
                            }
                        } catch (error) {
                            console.error('Error initializing server file select:', error);
                        }
                    });
                },

                isFileAllowed(file) {
                    // If acceptedFileTypes is empty, allow all file types
                    if (!this.acceptedFileTypes || this.acceptedFileTypes.length === 0) {
                        return true;
                    }
                    
                    // Check file extension
                    const fileExtension = file.extension.toLowerCase();
                    const videoExtensions = ['mp4', 'avi', 'mov', 'mpeg'];
                    
                    // If we're looking for videos and this is a common video extension
                    if (this.acceptedFileTypes.some(type => type.includes('video/')) && 
                        videoExtensions.includes(fileExtension)) {
                        return true;
                    }
                    
                    // Check MIME type mapping
                    const extensionToMime = {
                        'mp4': 'video/mp4',
                        'avi': 'video/avi',
                        'mov': 'video/mov',
                        'mpeg': 'video/mpeg',
                        // Add more mappings as needed
                    };
                    
                    return this.acceptedFileTypes.includes(extensionToMime[fileExtension]);
                },

                handleItemClick(item) {
                    if (item.type === 'directory') {
                        // Navigate into directory
                        this.navigateToDirectory(item.relative_path);
                    } else if (item.type === 'file') {
                        // Handle file selection
                        this.selectFile(item.path);
                        
                        // Set as active preview
                        if (this.enablePreview) {
                            this.activePreview = item;
                        }
                    }
                },
                
                isSelected(path) {
                    return this.selectedFiles.includes(path);
                },
                
                previewVideo(item) {
                    this.activePreview = item;
                    
                    // Ensure video loads and plays
                    this.$nextTick(() => {
                        if (this.$refs.videoPreview) {
                            const video = this.$refs.videoPreview;
                            
                            // Reset video
                            video.pause();
                            video.currentTime = 0;
                            
                            // Load and play
                            video.load();
                            const playPromise = video.play();
                            
                            if (playPromise !== undefined) {
                                playPromise.then(() => {
                                    console.log('Video started playing');
                                }).catch(error => {
                                    console.error('Error playing video:', error);
                                    // Try to play without user interaction
                                    video.play().catch(e => console.error('Second attempt failed:', e));
                                });
                            }
                        }
                    });
                },
                
                formatFileSize(bytes) {
                    if (!bytes) return '0 Bytes';
                    
                    const k = 1024;
                    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    const i = Math.floor(Math.log(bytes) / Math.log(k));
                    
                    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
                },

                navigateToDirectory(path) {
                    // Update current path
                    this.currentPath = path;
                    
                    // Reset active preview
                    this.activePreview = null;
                    
                    // Fetch files for new directory
                    this.$wire.call('getServerFiles', path)
                        .then(newFiles => {
                            this.files = newFiles.filter(item => item.type === 'directory' || this.isFileAllowed(item));
                        })
                        .catch(error => {
                            console.error('Error navigating to directory:', error);
                        });
                },

                navigateToParentDirectory() {
                    if (!this.currentPath) return;

                    // Remove last segment of the path
                    const pathSegments = this.currentPath.split('/');
                    pathSegments.pop();
                    const parentPath = pathSegments.join('/');

                    this.currentPath = parentPath;
                    
                    // Reset active preview
                    this.activePreview = null;
                    
                    // Fetch files for parent directory
                    this.$wire.call('getServerFiles', parentPath)
                        .then(newFiles => {
                            this.files = newFiles.filter(item => item.type === 'directory' || this.isFileAllowed(item));
                        })
                        .catch(error => {
                            console.error('Error navigating to parent directory:', error);
                        });
                },

                selectFile(path) {
                    if (this.isMultiple) {
                        const index = this.selectedFiles.indexOf(path);
                        if (index > -1) {
                            // Deselect if already selected
                            this.selectedFiles.splice(index, 1);
                        } else {
                            // Select file
                            this.selectedFiles.push(path);
                        }
                    } else {
                        // Single selection mode
                        this.selectedFiles = [path];
                    }

                    // Update Livewire state
                    try {
                        if (this.isMultiple) {
                            this.$wire.set(this.statePath, this.selectedFiles);
                        } else {
                            this.$wire.set(this.statePath, path);
                        }
                    } catch (error) {
                        console.error('Error setting Livewire state:', error);
                    }
                },

                removeFile(index) {
                    this.selectedFiles.splice(index, 1);
                    
                    // Update Livewire state
                    try {
                        if (this.isMultiple) {
                            this.$wire.set(this.statePath, this.selectedFiles);
                        } else {
                            this.$wire.set(this.statePath, null);
                        }
                    } catch (error) {
                        console.error('Error removing file:', error);
                    }
                },

                getFilenameFromPath(path) {
                    return path.split('/').pop();
                },

                getVideoMimeType(extension) {
                    const mimeTypes = {
                        'mp4': 'video/mp4',
                        'avi': 'video/avi',
                        'mov': 'video/mov',
                        'mpeg': 'video/mpeg'
                    };
                    return mimeTypes[extension] || 'video/mp4';
                }
            }
        }
    </script>
    @endpush
</div>