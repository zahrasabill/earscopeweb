<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\Field;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ServerFileSelect extends Field
{
    protected string $view = 'filament.forms.components.server-file-select';
    
    protected string|array|null $directory = null;
    
    protected array $allowedExtensions = ['*'];
    
    protected array $acceptedFileTypes = [];
    
    protected bool $multiple = false;
    
    protected bool $filterHidden = true;
    
    protected bool $enablePreview = false;
    
    protected string $previewType = 'image';
    
    public function directory(string|array $directory): static
    {
        $this->directory = $directory;
        return $this;
    }
    
    public function allowedExtensions(array $extensions): static
    {
        $this->allowedExtensions = $extensions;
        return $this;
    }
    
    public function acceptedFileTypes(array $types): static
    {
        $this->acceptedFileTypes = $types;
        return $this;
    }
    
    public function enablePreview(bool $enablePreview = true): static
    {
        $this->enablePreview = $enablePreview;
        return $this;
    }
    
    public function previewType(string $previewType): static
    {
        $this->previewType = $previewType;
        return $this;
    }
    
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        return $this;
    }
    
    public function filterHidden(bool $filterHidden = true): static
    {
        $this->filterHidden = $filterHidden;
        return $this;
    }
    
    public function getDirectory(): array
    {
        $directories = is_array($this->directory) ? $this->directory : [$this->directory];
        
        return array_map(function ($dir) {
            return rtrim(storage_path('app/public/' . $dir), '/');
        }, $directories);
    }
    
    public function getServerFiles(?string $currentPath = null): array
    {
        $basePaths = $this->getDirectory();
        $files = [];
        
        foreach ($basePaths as $basePath) {
            // If a current path is provided, use it; otherwise, use the base path
            $searchPath = $currentPath ? $basePath . '/' . $currentPath : $basePath;
            
            if (!File::exists($searchPath)) {
                continue;
            }
            
            $directoryContents = File::files($searchPath);
            $subdirectories = File::directories($searchPath);
            
            // Process files
            foreach ($directoryContents as $file) {
                // Filter hidden files if configured
                if ($this->filterHidden && Str::startsWith($file->getFilename(), '.')) {
                    continue;
                }
                
                // Filter by extensions
                if ($this->allowedExtensions !== ['*'] && 
                    !in_array($file->getExtension(), $this->allowedExtensions)) {
                    continue;
                }
                
                // Dapatkan path relatif dari storage/app/public
                $relativePath = Str::after($file->getPathname(), storage_path('app/public/'));
                
                $files[] = [
                    'type' => 'file',
                    'path' => $relativePath,
                    'relative_path' => $relativePath,
                    'filename' => $file->getFilename(),
                    'extension' => $file->getExtension(),
                    'size' => $file->getSize(),
                    'preview_url' => $this->enablePreview ? $this->getPreviewUrl($relativePath) : null,
                ];
            }
            
            // Process directories
            foreach ($subdirectories as $directory) {
                $relativePath = Str::after($directory, storage_path('app/public/'));
                
                $files[] = [
                    'type' => 'directory',
                    'path' => $relativePath,
                    'relative_path' => $relativePath,
                    'filename' => basename($directory),
                ];
            }
        }
        
        return $files;
    }

    public function isMultipleMode(): bool
    {
        return $this->multiple;
    }
    
    public function isPreviewEnabled(): bool
    {
        return $this->enablePreview;
    }
    
    public function getPreviewType(): string
    {
        return $this->previewType;
    }
    
    public function getAcceptedFileTypes(): array
    {
        return $this->acceptedFileTypes;
    }
    
    protected function getPreviewUrl(string $path): string
    {
        // Hapus path lengkap sistem file jika ada
        $path = preg_replace('#^.*?storage/app/public/#', '', $path);
        // Hapus directory dari path jika sudah ada
        $path = preg_replace('#^' . $this->directory . '/#', '', $path);
        return Storage::url($path);
    }

    // Override getStatePath to match parent method signature
    public function getStatePath(bool $isAbsolute = true): string
    {
        return parent::getStatePath($isAbsolute);
    }
}