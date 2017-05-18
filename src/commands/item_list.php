<?php

require_once __DIR__ . '/../../vendor/autoload.php';

use AlfredTime\Timer;
use AlfredTime\Config;
use Alfred\Workflows\Workflow;
use AlfredTime\WorkflowHandler;

$workflow = new Workflow();
$config = new Config(getenv('alfred_workflow_data') . '/config.json');
$timer = new Timer($config);
$workflowHandler = new WorkflowHandler($config);

$query = getenv('description');

$type = substr($argv[1], 0, -1);

$items = call_user_func([$workflowHandler, 'get' . ucfirst($argv[1])]);

if (substr($query, 0, 6) === 'start ') {
    $workflow->result()
        ->arg(json_encode([]))
        ->title('No ' . $type)
        ->subtitle('Timer will be created without a ' . $type)
        ->type('default')
        ->valid(true);

    $items = array_filter($items, function ($value) use ($timer) {
        return isset($value[$timer->getPrimaryService()]);
    });
} elseif (substr($query, 0, 10) === 'start_all ') {
    $activatedServices = $config->activatedServices();

    foreach ($items as $name => $services) {
        if (count($activatedServices) !== count($services)) {
            unset($items[$name]);
        }
    }
}

foreach ($items as $name => $ids) {
    $subtitle = ucfirst($type) . ' available for ' . implode(' and ', array_map(function ($value) {
        return ucfirst($value);
    }, array_keys($ids)));

    $item = $workflow->result()
        ->arg(json_encode($ids))
        ->title($name)
        ->subtitle($subtitle)
        ->type('default')
        ->valid(true);

    if (count($ids) === 1) {
        $item->icon('icons/' . key($ids) . '.png');
    }
}

echo $workflow->output();