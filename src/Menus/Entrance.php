<?php

namespace Godbout\Alfred\Time\Menus;

use Godbout\Alfred\Time\Workflow;
use Godbout\Alfred\Workflow\Icon;
use Godbout\Alfred\Workflow\Item;
use Godbout\Alfred\Workflow\Mods\Cmd;
use Godbout\Alfred\Workflow\ScriptFilter;

class Entrance extends Menu
{
    public static function scriptFilter()
    {
        $serviceEnabled = Workflow::serviceEnabled();
        if (! $serviceEnabled) {
            return;
        }
        if ($runningTimer = $serviceEnabled->runningTimer()) {
            ScriptFilter::add(
							self::startTimer(),
							self::stopCurrentTimer($runningTimer),
						);
        } else {
						ScriptFilter::add(
							self::startTimer()
						);
        }
    }

    private static function stopCurrentTimer($runningTimer)
    {
        return Item::create()
            ->uid('stop_timer')
            ->title('Stop "' . $runningTimer->description . '"')
            ->subtitle(floor((time() + $runningTimer->duration) / 60) . ' mins')
            ->arg('do')
            ->variable('action', 'stop');
    }

    private static function startTimer()
    {
        if (! empty(Workflow::serviceEnabled())) {
            return Item::create()
                ->uid('start_timer')
                ->title('Start "' . self::userInput() . '"')
                ->mod(
                    Cmd::create()
                        ->subtitle('Continue a timer')
                        ->arg('choose_timer')
                )
								->arg('choose_project')
                ->variable('timer_description', self::userInput());
        }
    }

    private static function setupWorkflow()
    {
        if (empty(Workflow::serviceEnabled()) || (empty(self::userInput()))) {
            return Item::create()
                ->uid('setup_timers')
                ->title('Setup the workflow')
                ->arg('setup')
                ->icon(Icon::create('resources/icons/icon.png'));
        }
    }
}
