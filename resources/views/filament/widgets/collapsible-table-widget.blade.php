<x-filament-widgets::widget>
    <x-filament::section
        :heading="$this->getWidgetHeading()"
        collapsible
        persist-collapsed
    >
        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>
