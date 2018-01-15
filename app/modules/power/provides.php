<?php

return array(
    'client_tabs' => array(
        'power-tab' => array('view' => 'power::power_tab', 'i18n' => 'power.power'),
        'battery-tab' => array('view' => 'power::battery_tab', 'i18n' => 'power.battery'),
    ),
    'listings' => array(
        'batteries' => array('view' => 'batteries_listing', 'i18n' => 'power.batteries'),
        'power' => array('view' => 'power_listing', 'i18n' => 'power.power'),
    ),
    'widgets' => array(
        'power_battery_condition' => array('view' => 'power::power_battery_condition_widget'),
        'power_battery_health' => array('view' => 'power::power_battery_health_widget'),
    ),
    'reports' => array(
        'power' => array('view' => 'power', 'i18n' => 'power.report'),
    ),
);
