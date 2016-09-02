<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Search Engine
    |--------------------------------------------------------------------------
    |
    | This option controls the default search connection that gets used while
    | using Larasearch. This connection is used when syncing all models
    | to the search service. You should adjust this based on your needs.
    |
    | Supported: "algolia", "elastic", "null"
    |
    */

    'driver' => env('SCOUT_DRIVER', 'algolia'),

    /*
    |--------------------------------------------------------------------------
    | Index Prefix
    |--------------------------------------------------------------------------
    |
    | Here you may specify a prefix that will be applied to all search index
    | names used by Scout. This prefix may be useful if you have multiple
    | "tenants" or applications sharing the same search infrastructure.
    |
    */

    'prefix' => env('SCOUT_PREFIX', ''),

    /*
    |--------------------------------------------------------------------------
    | Queue Data Syncing
    |--------------------------------------------------------------------------
    |
    | This option allows you to control if the operations that sync your data
    | with your search engines are queued. When this is set to "true" then
    | all automatic data syncing will get queued for better performance.
    |
    */

    'queue' => false,

    /*
    |--------------------------------------------------------------------------
    | Algolia Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Algolia settings. Algolia is a cloud hosted
    | search engine which works great with Scout out of the box. Just plug
    | in your application ID and admin API key to get started searching.
    |
    */

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    /*
    |--------------------------------------------------------------------------
    | Elastic Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your Elastic settings.
    |
    */
    'elastic' => [

        'index' => env('ELASTIC_INDEX'),
    
        'hosts' => [
            env('ELASTIC_HOST', '127.0.0.1').':'.env('ELASTIC_PORT', 9200)
        ],

        /**
         * SSL Verification
         *
         * If your Elasticsearch instance uses an out-dated or self-signed SSL
         * certificate, you will need to pass in the certificate bundle. This can
         * either be the path to the certificate file (for self-signed certs), or a
         * package like https://github.com/Kdyby/CurlCaBundle. See the documentation
         * below for all the details.
         *
         * If you are using SSL instances, and the certificates are up-to-date and
         * signed by a public certificate authority, then you can leave this null and
         * just use "https" in the host path(s) above and you should be fine.
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/2.0/_security.html#_ssl_encryption_2
         */
        'ssl_verification' => null,

        /**
         * Retries
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_set_retries
         */
        'retries' => 0,

        /**
         * Logger
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#enabling_logger
         */
        'log' => false,

        // 'logger' => Log::getMonolog(),

        'log_path' => storage_path('logs/elastic.log'),

        'log_level' => Monolog\Logger::WARNING,

        /**
         * HTTP Handler
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_configure_the_http_handler
         * @see http://ringphp.readthedocs.io/en/latest/client_handlers.html
         *
         * Available Settings: "default", "multi", "single"
         */
        'handler' => 'default',

        /**
         * Connection Pool
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_setting_the_connection_pool
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_connection_pool.html
         */
        'connection_pool' => '\Elasticsearch\ConnectionPool\StaticNoPingConnectionPool',

        /**
         * Connection Selector
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_setting_the_connection_selector
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_selectors.html
         */
        'selector' => '\Elasticsearch\ConnectionPool\Selectors\StickyRoundRobinSelector',

        /**
         * Serializer
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_setting_the_serializer
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_serializers.html
         */
        'serializer' => '\Elasticsearch\Serializers\SmartSerializer',

        /**
         * Connection Factory
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_setting_a_custom_connectionfactory
         */
        'connection_factory' => null,

        /**
         * Endpoint Closure
         *
         * @see https://www.elastic.co/guide/en/elasticsearch/client/php-api/current/_configuration.html#_set_the_endpoint_closure
         */
        'endpoint' => null,
    ],

];
