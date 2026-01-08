<x-filament-panels::page>
    ステータスが「審査中」ならば「リリース中」に変更する<br>
	ステータスが「リリース中」ならば「審査中」に変更する

	<x-filament-panels::form wire:submit="save">
		{{ $this->form }}

		<x-filament-panels::form.actions
			:actions="$this->getCachedFormActions()"
			:full-width="$this->hasFullWidthFormActions()"
		/>
	</x-filament-panels::form>
</x-filament-panels::page>
