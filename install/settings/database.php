<?php
/* settings/database.php */

return [
    'mysql' => [
        'dbdriver' => 'mysql',
        'username' => 'root',
        'password' => 'oncblog!Admin!',
        'dbname' => 'db_eoffice_edms',
        'prefix' => 'tbl'
    ],
    'tables' => [
        'category' => 'category',
        'edocument' => 'edocument',
        'edocument_download' => 'edocument_download',
        'edms' => 'edms',
        'edms_files' => 'edms_files',
        'edms_download' => 'edms_download',
        'edms_meta' => 'edms_meta',
        'inventory'=> 'inventory',
        'inventory_meta'=>'inventory_meta',
        'inventory_user' => 'inventory_user',
        'language' => 'language',
        'logs' => 'logs',
        'user' => 'user',
        'user_meta' => 'user_meta',
        'car_reservation' => 'car_reservation',
        'car_reservation_data' => 'car_reservation_data',
        'vehicles' => 'vehicles',
        'vehicles_meta' => 'vehicles_meta'
    ]
];
