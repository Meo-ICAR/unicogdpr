<x-filament-widgets::widget>
    <x-filament::section
        :heading="$this->getWidgetHeading()"
        :collapsed="$this->isWidgetCollapsedByDefault()"
        collapsible
        persist-collapsed
    >
        @if ($badge = $this->getWidgetBadge())
            <x-slot name="afterHeader">
                <x-filament::badge :color="$this->getWidgetBadgeColor()">
                    {{ $badge }}
                </x-filament::badge>
            </x-slot>
        @endif

        {{ $this->table }}
    </x-filament::section>
</x-filament-widgets::widget>
