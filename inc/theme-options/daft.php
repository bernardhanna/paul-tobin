<?php
use StoutLogic\AcfBuilder\FieldsBuilder;

$fields = new FieldsBuilder('daft_settings');

$fields
    ->addAccordion('daft_settings_start', [
        'label' => 'Daft API Import Settings',
    ])
    ->addSelect('daft_request_mode', [
        'label' => 'Request Mode',
        'choices' => [
            'mock' => 'Mock (local development)',
            'soap' => 'SOAP (Daft search operations)',
            'rest' => 'REST (custom endpoint URL)',
        ],
        'default_value' => 'mock',
        'ui' => 1,
        'return_format' => 'value',
    ])
    ->addPassword('daft_api_key', [
        'label' => 'API Key',
        'instructions' => 'Paste your Daft API key. This field is masked in admin.',
        'required' => 1,
    ])
    ->addText('daft_soap_wsdl_url', [
        'label' => 'SOAP WSDL URL',
        'instructions' => 'Daft sample code uses this WSDL URL.',
        'default_value' => 'http://api.daft.ie/v2/wsdl.xml',
    ])->conditional('daft_request_mode', '==', 'soap')
    ->addSelect('daft_soap_operation', [
        'label' => 'SOAP Operation',
        'choices' => [
            'search_sale' => 'search_sale',
            'search_rental' => 'search_rental',
            'search_commercial' => 'search_commercial',
            'search_new_development' => 'search_new_development',
            'search_shortterm' => 'search_shortterm',
            'search_sharing' => 'search_sharing',
            'search_parking' => 'search_parking',
        ],
        'default_value' => 'search_sale',
        'ui' => 1,
        'return_format' => 'value',
    ])->conditional('daft_request_mode', '==', 'soap')
    ->addTextarea('daft_soap_query_json', [
        'label' => 'SOAP Query JSON (Optional)',
        'instructions' => 'JSON object sent as the query argument, e.g. {\"bedrooms\":2}.',
        'rows' => 6,
        'new_lines' => '',
    ])->conditional('daft_request_mode', '==', 'soap')
    ->addTextarea('daft_mock_payload_json', [
        'label' => 'Mock Payload JSON (Optional)',
        'instructions' => 'Paste a mock Daft response JSON. If empty, bundled sample data will be used.',
        'rows' => 8,
        'new_lines' => '',
    ])->conditional('daft_request_mode', '==', 'mock')
    ->addTrueFalse('daft_mock_gallery_placeholders', [
        'label' => 'Use Placeholder Gallery Images (Mock Mode)',
        'instructions' => 'Testing helper: when Daft mock data has no extra gallery images, inject placeholder gallery images after Content One.',
        'ui' => 1,
        'default_value' => 1,
    ])->conditional('daft_request_mode', '==', 'mock')
    ->addText('daft_endpoint_url', [
        'label' => 'Listings Endpoint URL',
        'instructions' => 'Full Daft API endpoint used for imports (must be the exact Daft v3 route your account is enabled for).',
        'default_value' => '',
        'required' => 1,
    ])->conditional('daft_request_mode', '==', 'rest')
    ->addSelect('daft_http_method', [
        'label' => 'HTTP Method',
        'choices' => [
            'GET' => 'GET',
            'POST' => 'POST',
        ],
        'default_value' => 'GET',
        'ui' => 1,
        'return_format' => 'value',
    ])->conditional('daft_request_mode', '==', 'rest')
    ->addTextarea('daft_request_body_json', [
        'label' => 'Request Body JSON (Optional)',
        'instructions' => 'Use for POST endpoints only. Enter valid JSON.',
        'rows' => 6,
        'new_lines' => '',
    ])->conditional('daft_request_mode', '==', 'rest')
    ->addText('daft_api_key_header', [
        'label' => 'API Key Header Name',
        'instructions' => 'Most APIs use x-api-key or Authorization.',
        'default_value' => 'x-api-key',
    ])
    ->addText('daft_api_key_prefix', [
        'label' => 'API Key Prefix (Optional)',
        'instructions' => 'Example: Bearer. Leave blank for raw key.',
        'default_value' => '',
    ])
    ->addText('daft_status_default', [
        'label' => 'Default Property Status',
        'instructions' => 'Used when Daft payload has no status value.',
        'default_value' => 'For Sale',
    ])
    ->addText('daft_type_default', [
        'label' => 'Default Property Type',
        'instructions' => 'Used when Daft payload has no type value.',
        'default_value' => 'House',
    ])
    ->addTextarea('daft_known_server_ips', [
        'label' => 'Known Server IPs (one per line)',
        'instructions' => 'Add staging/current/live server public IPs for allowlist notes.',
        'rows' => 4,
        'new_lines' => '',
        'default_value' => "164.92.198.42",
    ])
    ->addTrueFalse('daft_update_images', [
        'label' => 'Refresh Featured Images',
        'instructions' => 'When enabled, sync will update featured image from Daft image URLs.',
        'ui' => 1,
        'default_value' => 0,
    ])
    ->addTrueFalse('daft_auto_sync_daily', [
        'label' => 'Enable Daily Auto Sync',
        'instructions' => 'Runs an automated import once per day.',
        'ui' => 1,
        'default_value' => 0,
    ])
    ->addNumber('daft_sync_batch_size', [
        'label' => 'Sync Batch Size',
        'instructions' => 'Number of listings processed per run (helps avoid staging timeouts).',
        'default_value' => 10,
        'min' => 1,
        'max' => 200,
        'step' => 1,
    ])
    ->addNumber('daft_sync_time_budget_seconds', [
        'label' => 'Sync Time Budget (seconds)',
        'instructions' => 'Importer stops this run when time budget is reached and continues next run.',
        'default_value' => 20,
        'min' => 5,
        'max' => 120,
        'step' => 1,
    ])
    ->addAccordion('daft_settings_end')
    ->endpoint();

return $fields;
