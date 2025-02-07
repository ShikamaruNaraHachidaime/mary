<?php

namespace Mary\View\Components;

use Carbon\CarbonPeriod;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Calendar extends Component
{
    public string $uuid;

    public function __construct(
        public ?int $months = 1,
        public ?string $locale = 'en-EN',
        public ?bool $weekendHighlight = false,
        public ?array $config = [],
        public ?array $events = [],
    ) {
        $this->uuid = md5(serialize($this));

    }

    public function setup(): string
    {
        $config = json_encode(array_merge([
            'type' => $this->months == 1 ? 'default' : 'multiple',
            'months' => $this->months,
            'popups' => $this->popups(),
            'settings' => [
                'lang' => $this->locale,
                'visibility' => [
                    'daysOutside' => false,
                    'weekend' => $this->weekendHighlight,
                ],
                'selection' => [
                    'day' => false,
                ],
            ],
            'CSSClasses' => 'y',
            'actions' => 'x',
        ], $this->config));

        $config = $this->addCss($config);
        $config = $this->addAction($config);

        return $config;
    }

    // Extra CSS for responsive layout
    public function addCss(string $config): string
    {
        return str_replace('"y"', '{"grid":"vanilla-calendar-grid flex flex-wrap justify-around","calendar":"vanilla-calendar"}', $config);
    }

    // Proper inject javascript function for arrow click work with `months step`
    public function addAction(string $config): string
    {
        $step = $this->months - 1;

        return str_replace('"x"',
            '{
                clickArrow(event, year, month) {
                    const isPrev = event.target.closest(\'[data-calendar-arrow="prev"]\');
                    const date = new Date(year, month);
                    date.setMonth(isPrev ? date.getMonth() - '.$step.' : date.getMonth() + '.$step.');
                    calendar.settings.selected.year = date.getFullYear();
                    calendar.settings.selected.month = date.getMonth();
                    calendar.update();
                }
            }', $config);
    }

    public function popups()
    {
        return collect($this->events)->flatMap(function ($event) {
            if ($range = $event['range'] ?? []) {
                $dates = [];

                $period = CarbonPeriod::create($range[0], $range[1]);

                foreach ($period as $date) {
                    $dates[] = $date->toDateString();
                }
            }

            if (isset($event['date'])) {
                $dates = [$event['date']];
            }

            return collect($dates)->flatMap(function ($date) use ($event) {
                return [
                    $date => [
                        'modifier' => $event['css'],
                        'html' => '<div><strong>'.$event['label'].'</strong></div><div>'.($event['description'] ?? null).'</div>',
                    ],
                ];
            });
        });
    }

    public function render(): View|Closure|string
    {
        return <<<'HTML'
            <div x-data x-init="const calendar = new VanillaCalendar($el, {{ $setup() }}); calendar.init()" class="w-fit">
            </div>   
            HTML;
    }
}
