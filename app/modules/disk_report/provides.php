<?php

return array(
    'client_tabs' => array(
        'storage-tab' => array(
            'view' => 'storage_tab',
            'i18n' => 'disk_report.storage',
            'view_controller' => 'DiskReportTabController'
        ),
    ),
    'listings' => array(
        'disk' => array('view' => 'disk_listing', 'i18n' => 'disk_report.storage'),
    ),
    'widgets' => array(
        'disk_report' => array('view' => 'disk_report::disk_report_widget'),
        'filevault' => array('view' => 'disk_report::filevault_widget'),
        'smart_status' => array('view' => 'disk_report::smart_status_widget'),
        'disk_type' => array('view' => 'disk_report::disk_type_widget'),
        'filesystem_type' => array('view' => 'disk_report::filesystem_type_widget'),
    ),
    'reports' => array(
        'storage' => array('view' => 'storage_report', 'i18n' => 'disk_report.report'),
    ),
);
