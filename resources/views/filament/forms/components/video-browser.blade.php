@php
    $videos = collect(File::files(storage_path('app/videos/raw')))
        ->filter(function($file) {
            return in_array(strtolower($file->getExtension()), ['mp4', 'avi', 'mov', 'mkv']);
        })
        ->map(function($file) {
            return [
                'name' => $file->getFilename(),
                'path' => 'videos/raw/' . $file->getFilename(),
                'url' => Storage::url('videos/raw/' . $file->getFilename()),
                'thumbnail' => asset('images/video-thumbnail.jpg'), // Gunakan default thumbnail
            ];
        });
@endphp

<div x-data="{ 
    selected: null,
    videos: @js($videos),
    filteredVideos: @js($videos),
    search: '',
    selectVideo(video) {
        this.selected = video;
        $wire.set('{{ $getStatePath() }}', video.path);
    },
    filterVideos() {
        if (!this.search) {
            this.filteredVideos = this.videos;
            return;
        }
        const searchTerm = this.search.toLowerCase();
        this.filteredVideos = this.videos.filter(video => 
            video.name.toLowerCase().includes(searchTerm)
        );
    }
}" class="p-4 bg-white rounded-lg border border-gray-300">
    <div class="mb-4">
        <input type="text" 
               placeholder="Cari video..." 
               class="w-full px-4 py-2 border rounded-lg"
               x-model="search"
               @input="filterVideos">
    </div>

    <div class="grid grid-cols-4 gap-4">
        <template x-for="video in filteredVideos" :key="video.path">
            <div @click="selectVideo(video)"
                 :class="{'ring-2 ring-primary-500': selected?.path === video.path}"
                 class="relative cursor-pointer rounded-lg overflow-hidden hover:ring-2 hover:ring-gray-300 transition-all">
                <img :src="video.thumbnail" 
                     :alt="video.name"
                     class="w-full aspect-video object-cover">
                <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white p-2 text-sm truncate">
                    <span x-text="video.name"></span>
                </div>
            </div>
        </template>
    </div>
</div> 