<?php

return [
    'services' => [
        [
            'services' => [
                'CalculatorService',
                'ProductService',
            ],
            'nodes' => [
                ['host' => '127.0.0.1', 'port' => 9503, 'path' => '/rpc'],
                ['host' => '127.0.0.1', 'port' => 9503, 'path' => '/rpc']
            ]
        ]
    ]
];
