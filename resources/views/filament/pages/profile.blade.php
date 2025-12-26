<?php
/**
 * Developed by eBrook Group.
 * Copyright © 2026 eBrook Group (https://www.ebrook.com.tw)
 */
?>

<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="fi-form-actions">
            <x-filament::actions
                :actions="$this->getFormActions()"
            />
        </div>
    </form>
</x-filament-panels::page>

