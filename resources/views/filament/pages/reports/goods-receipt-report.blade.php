<x-filament-panels::page>
    <div class="mb-6">
        <form wire:submit="$refresh">
            {{ $this->form }}
        </form>
    </div>

    <div>
        {{ $this->table }}
    </div>
</x-filament-panels::page>
