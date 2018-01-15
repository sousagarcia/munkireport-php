<?php

return array(
    'client_tabs' => array(
        'extensions-tab' => array('view' => 'extensions::extensions_tab', 'i18n' => 'extensions.clienttab', 'badge' => 'extensions-cnt'),
    ),
    'listings' => array(
        'extensions' => array('view' => 'extensions_listing', 'i18n' => 'extensions.clienttab'),
    ),
    'widgets' => array(
        'extensions' => array('view' => 'extensions::extensions_widget'),
        'extensions_codesign' => array('view' => 'extensions::extensions_codesign_widget'),
    ),
    'reports' => array(
        'extensions' => array('view' => 'extensions_report', 'i18n' => 'extensions.report'),
    ),
);
