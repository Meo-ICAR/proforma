<?php

// config for EightyNine/ExcelImportAction
return [
    /**
     * File upload path
     *
     * Customise the path where the file will be uploaded to,
     * if left empty, config('filesystems.default') will be used
     */
    'upload_disk' => null,

    /**
     * Load custom stylesheet
     *
     * Set to false to disable loading the custom CSS to prevent conflicts
     * with existing button styles in your application
     */
    'load_stylesheet' => false,
    'import_action_heading' => 'Import Fatture',
    'import_action_description' => 'Carica un file Excel per importare le fatture.',
    'excel_data' => 'Dati Excel',
    'columns' => [
        'column_name' => 'Nome Colonna',
        'sample_data' => 'Dati di Esempio',
        'map_to' => 'Mappa a',
    ],
];
