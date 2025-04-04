<div class="min-h-screen flex">
    <!-- Left Section (Logo & Branding) -->
    <div class="hidden lg:flex flex-col items-center justify-center w-1/2 bg-[#004178] relative">
        <div class="absolute top-0 left-0 w-full h-full">
            <div class="absolute w-full h-full bg-success"></div>
            <div class="absolute w-full h-full bg-accent -left-[38px]"></div>
            <div class="absolute w-full h-full bg-secondary -left-[73px]"></div>
        </div>

        <div class="p-8 max-w-md w-full z-10">
            <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full">
        </div>
    </div>

    <!-- Right Section (Login Form) -->
    <div class="w-full lg:w-1/2 flex flex-col items-center justify-center p-8">
        <div class="p-8 max-w-md w-full">
            <h1 class="text-3xl font-bold text-[#004178] text-center mb-4">Selamat Datang!</h1>
            <p class="text-gray-600 text-center mb-6">Silahkan masukkan Kode Akses dan Password Anda</p>

            <form wire:submit="authenticate" class="space-y-4">
                {{ $this->form }}

                <x-filament-panels::form.actions
                    :actions="$this->getCachedFormActions()"
                    :full-width="$this->hasFullWidthFormActions()"
                />
            </form>
        </div>
    </div>
</div>
