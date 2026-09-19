<?php return array (
  'broadcasting' => 
  array (
    'default' => 'null',
    'connections' => 
    array (
      'reverb' => 
      array (
        'driver' => 'reverb',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'host' => NULL,
          'port' => 443,
          'scheme' => 'https',
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => NULL,
        'secret' => NULL,
        'app_id' => NULL,
        'options' => 
        array (
          'cluster' => NULL,
          'host' => 'api-mt1.pusher.com',
          'port' => 443,
          'scheme' => 'https',
          'encrypted' => true,
          'useTLS' => true,
        ),
        'client_options' => 
        array (
        ),
      ),
      'ably' => 
      array (
        'driver' => 'ably',
        'key' => NULL,
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'concurrency' => 
  array (
    'default' => 'process',
  ),
  'cors' => 
  array (
    'paths' => 
    array (
      0 => 'api/*',
      1 => 'sanctum/csrf-cookie',
    ),
    'allowed_methods' => 
    array (
      0 => '*',
    ),
    'allowed_origins' => 
    array (
      0 => '*',
    ),
    'allowed_origins_patterns' => 
    array (
    ),
    'allowed_headers' => 
    array (
      0 => '*',
    ),
    'exposed_headers' => 
    array (
    ),
    'max_age' => 0,
    'supports_credentials' => false,
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 12,
      'verify' => true,
      'limit' => NULL,
    ),
    'argon' => 
    array (
      'memory' => 65536,
      'threads' => 1,
      'time' => 4,
      'verify' => true,
    ),
    'rehash_on_login' => true,
  ),
  'mail' => 
  array (
    'default' => 'log',
    'mailers' => 
    array (
      'smtp' => 
      array (
        'transport' => 'smtp',
        'scheme' => NULL,
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '2525',
        'username' => NULL,
        'password' => NULL,
        'timeout' => NULL,
        'local_domain' => 'localhost',
      ),
      'ses' => 
      array (
        'transport' => 'ses',
      ),
      'postmark' => 
      array (
        'transport' => 'postmark',
      ),
      'resend' => 
      array (
        'transport' => 'resend',
      ),
      'sendmail' => 
      array (
        'transport' => 'sendmail',
        'path' => '/usr/sbin/sendmail -bs -i',
      ),
      'log' => 
      array (
        'transport' => 'log',
        'channel' => NULL,
      ),
      'array' => 
      array (
        'transport' => 'array',
      ),
      'failover' => 
      array (
        'transport' => 'failover',
        'mailers' => 
        array (
          0 => 'smtp',
          1 => 'log',
        ),
        'retry_after' => 60,
      ),
      'roundrobin' => 
      array (
        'transport' => 'roundrobin',
        'mailers' => 
        array (
          0 => 'ses',
          1 => 'postmark',
        ),
        'retry_after' => 60,
      ),
    ),
    'from' => 
    array (
      'address' => 'hello@example.com',
      'name' => 'AutoCare Pro',
    ),
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\resources\\views/vendor/mail',
      ),
      'extensions' => 
      array (
      ),
    ),
  ),
  'app' => 
  array (
    'name' => 'AutoCare Pro',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://localhost',
    'frontend_url' => 'http://localhost:3000',
    'asset_url' => NULL,
    'timezone' => 'Asia/Colombo',
    'locale' => 'en',
    'fallback_locale' => 'en',
    'faker_locale' => 'en_US',
    'cipher' => 'AES-256-CBC',
    'key' => 'base64:KIquG8BNcaY7LFTlZEkCXELeemfUQVidCfNIdTCILEA=',
    'previous_keys' => 
    array (
    ),
    'maintenance' => 
    array (
      'driver' => 'file',
    ),
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Concurrency\\ConcurrencyServiceProvider',
      6 => 'Illuminate\\Cookie\\CookieServiceProvider',
      7 => 'Illuminate\\Database\\DatabaseServiceProvider',
      8 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      9 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      10 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      11 => 'Illuminate\\Hashing\\HashServiceProvider',
      12 => 'Illuminate\\Mail\\MailServiceProvider',
      13 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      14 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      15 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      16 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      17 => 'Illuminate\\Queue\\QueueServiceProvider',
      18 => 'Illuminate\\Redis\\RedisServiceProvider',
      19 => 'Illuminate\\Session\\SessionServiceProvider',
      20 => 'Illuminate\\Translation\\TranslationServiceProvider',
      21 => 'Illuminate\\Validation\\ValidationServiceProvider',
      22 => 'Illuminate\\View\\ViewServiceProvider',
      23 => 'App\\Providers\\AppServiceProvider',
      24 => 'App\\Providers\\TenancyServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Arr' => 'Illuminate\\Support\\Arr',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Benchmark' => 'Illuminate\\Support\\Benchmark',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Concurrency' => 'Illuminate\\Support\\Facades\\Concurrency',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Context' => 'Illuminate\\Support\\Facades\\Context',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'Date' => 'Illuminate\\Support\\Facades\\Date',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Http' => 'Illuminate\\Support\\Facades\\Http',
      'Js' => 'Illuminate\\Support\\Js',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Number' => 'Illuminate\\Support\\Number',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Process' => 'Illuminate\\Support\\Facades\\Process',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'RateLimiter' => 'Illuminate\\Support\\Facades\\RateLimiter',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schedule' => 'Illuminate\\Support\\Facades\\Schedule',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'Str' => 'Illuminate\\Support\\Str',
      'Uri' => 'Illuminate\\Support\\Uri',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Vite' => 'Illuminate\\Support\\Facades\\Vite',
    ),
    'deploy_secret' => NULL,
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'central' => 
      array (
        'driver' => 'session',
        'provider' => 'central_admins',
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\User',
      ),
      'central_admins' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\CentralAdmin',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60,
        'throttle' => 60,
      ),
    ),
    'password_timeout' => 10800,
  ),
  'cache' => 
  array (
    'default' => 'database',
    'stores' => 
    array (
      'array' => 
      array (
        'driver' => 'array',
        'serialize' => false,
      ),
      'session' => 
      array (
        'driver' => 'session',
        'key' => '_cache',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'cache',
        'lock_connection' => NULL,
        'lock_table' => 'cache_locks',
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\framework/cache/data',
        'lock_path' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'cache',
        'lock_connection' => 'default',
      ),
      'dynamodb' => 
      array (
        'driver' => 'dynamodb',
        'key' => NULL,
        'secret' => NULL,
        'region' => 'us-east-1',
        'table' => 'cache',
        'endpoint' => NULL,
      ),
      'octane' => 
      array (
        'driver' => 'octane',
      ),
      'failover' => 
      array (
        'driver' => 'failover',
        'stores' => 
        array (
          0 => 'database',
          1 => 'array',
        ),
      ),
    ),
    'prefix' => 'autocare-pro-cache',
  ),
  'database' => 
  array (
    'default' => 'mysql',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'url' => NULL,
        'database' => 'autocare',
        'prefix' => '',
        'foreign_key_constraints' => true,
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'autocare',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'mariadb' => 
      array (
        'driver' => 'mariadb',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'autocare',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'prefix_indexes' => true,
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
        ),
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'autocare',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'search_path' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'url' => NULL,
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'autocare',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
      ),
    ),
    'migrations' => 
    array (
      'table' => 'migrations',
      'update_date_on_publish' => true,
    ),
    'redis' => 
    array (
      'client' => 'phpredis',
      'default' => 
      array (
        'url' => NULL,
        'host' => '127.0.0.1',
        'username' => NULL,
        'password' => NULL,
        'port' => 6379,
        'database' => 0,
      ),
    ),
  ),
  'filesystems' => 
  array (
    'default' => 'public',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\app/private',
        'serve' => false,
        'throw' => false,
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\app/public',
        'url' => 'http://localhost/storage',
        'visibility' => 'public',
        'throw' => false,
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => NULL,
        'secret' => NULL,
        'region' => NULL,
        'bucket' => NULL,
        'url' => NULL,
        'endpoint' => NULL,
        'use_path_style_endpoint' => false,
        'throw' => false,
        'report' => false,
      ),
    ),
    'links' => 
    array (
      'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\public\\storage' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\app/public',
    ),
  ),
  'logging' => 
  array (
    'default' => 'stack',
    'deprecations' => 
    array (
      'channel' => 'null',
      'trace' => false,
    ),
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
        'ignore_exceptions' => false,
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\logs/laravel.log',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 14,
        'replace_placeholders' => true,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'papertrail' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\SyslogUdpHandler',
        'handler_with' => 
        array (
          'host' => NULL,
          'port' => NULL,
          'connectionString' => 'tls://:',
        ),
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'level' => 'debug',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'handler_with' => 
        array (
          'stream' => 'php://stderr',
        ),
        'formatter' => NULL,
        'processors' => 
        array (
          0 => 'Monolog\\Processor\\PsrLogMessageProcessor',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
        'facility' => 8,
        'replace_placeholders' => true,
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
        'replace_placeholders' => true,
      ),
      'null' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\NullHandler',
      ),
      'emergency' => 
      array (
        'path' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\logs/laravel.log',
      ),
    ),
  ),
  'queue' => 
  array (
    'default' => 'database',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'connection' => NULL,
        'table' => 'queue_jobs',
        'queue' => 'default',
        'retry_after' => 90,
        'after_commit' => false,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => 0,
        'after_commit' => false,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => NULL,
        'secret' => NULL,
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'default',
        'suffix' => NULL,
        'region' => 'us-east-1',
        'after_commit' => false,
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
        'after_commit' => false,
      ),
      'deferred' => 
      array (
        'driver' => 'deferred',
      ),
      'failover' => 
      array (
        'driver' => 'failover',
        'connections' => 
        array (
          0 => 'database',
          1 => 'deferred',
        ),
      ),
    ),
    'batching' => 
    array (
      'database' => 'mysql',
      'table' => 'job_batches',
    ),
    'failed' => 
    array (
      'driver' => 'database-uuids',
      'database' => 'mysql',
      'table' => 'failed_jobs',
    ),
  ),
  'services' => 
  array (
    'postmark' => 
    array (
      'token' => NULL,
    ),
    'resend' => 
    array (
      'key' => NULL,
    ),
    'ses' => 
    array (
      'key' => NULL,
      'secret' => NULL,
      'region' => 'us-east-1',
    ),
    'slack' => 
    array (
      'notifications' => 
      array (
        'bot_user_oauth_token' => NULL,
        'channel' => NULL,
      ),
    ),
    'whatsapp' => 
    array (
      'provider' => 'none',
      'url' => '',
      'token' => '',
      'phone_number_id' => '',
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => 120,
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'autocare-pro-session',
    'path' => '/',
    'domain' => NULL,
    'secure' => NULL,
    'http_only' => true,
    'same_site' => 'lax',
    'partitioned' => false,
  ),
  'tenancy' => 
  array (
    'central_prefix' => 'admin',
    'bootstrap_slugs' => 
    array (
    ),
    'base_domain' => NULL,
    'min_base_labels' => 2,
  ),
  'tenancy-clone' => 
  array (
    'tables' => 
    array (
      'roles' => 
      array (
        'fks' => 
        array (
        ),
      ),
      'users' => 
      array (
        'fks' => 
        array (
        ),
      ),
      'settings' => 
      array (
        'fks' => 
        array (
          'updated_by' => 'users',
        ),
      ),
      'tenant_modules' => 
      array (
        'fks' => 
        array (
        ),
      ),
      'businesses' => 
      array (
        'fks' => 
        array (
        ),
      ),
      'branches' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
        ),
      ),
      'categories' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'parent_id' => 'categories',
        ),
      ),
      'customers' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'branch_id' => 'branches',
        ),
      ),
      'vehicles' => 
      array (
        'fks' => 
        array (
          'customer_id' => 'customers',
        ),
      ),
      'service_categories' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
        ),
      ),
      'services' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'service_category_id' => 'service_categories',
        ),
      ),
      'service_prices' => 
      array (
        'fks' => 
        array (
          'service_id' => 'services',
          'branch_id' => 'branches',
        ),
      ),
      'vehicle_categories' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
        ),
      ),
      'service_vehicle_pricing' => 
      array (
        'fks' => 
        array (
          'service_id' => 'services',
          'vehicle_category_id' => 'vehicle_categories',
          'branch_id' => 'branches',
        ),
      ),
      'service_packages' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
        ),
      ),
      'package_items' => 
      array (
        'fks' => 
        array (
          'service_package_id' => 'service_packages',
          'service_id' => 'services',
        ),
      ),
      'products' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'category_id' => 'categories',
        ),
      ),
      'inventory' => 
      array (
        'fks' => 
        array (
          'product_id' => 'products',
          'branch_id' => 'branches',
        ),
      ),
      'inventory_movements' => 
      array (
        'fks' => 
        array (
          'product_id' => 'products',
          'branch_id' => 'branches',
          'user_id' => 'users',
        ),
      ),
      'suppliers' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
        ),
      ),
      'purchase_orders' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'branch_id' => 'branches',
          'supplier_id' => 'suppliers',
        ),
      ),
      'purchase_order_items' => 
      array (
        'fks' => 
        array (
          'purchase_order_id' => 'purchase_orders',
          'product_id' => 'products',
        ),
      ),
      'goods_receipts' => 
      array (
        'fks' => 
        array (
          'purchase_order_id' => 'purchase_orders',
          'branch_id' => 'branches',
        ),
      ),
      'supplier_returns' => 
      array (
        'fks' => 
        array (
          'supplier_id' => 'suppliers',
          'purchase_order_id' => 'purchase_orders',
          'branch_id' => 'branches',
        ),
      ),
      'appointments' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'branch_id' => 'branches',
          'customer_id' => 'customers',
          'vehicle_id' => 'vehicles',
        ),
      ),
      'jobs' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'branch_id' => 'branches',
          'customer_id' => 'customers',
          'vehicle_id' => 'vehicles',
          'appointment_id' => 'appointments',
          'technician_id' => 'users',
        ),
      ),
      'inspections' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
        ),
      ),
      'inspection_photos' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'uploaded_by' => 'users',
        ),
      ),
      'damage_records' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
        ),
      ),
      'job_services' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'service_id' => 'services',
        ),
      ),
      'job_parts' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'product_id' => 'products',
        ),
      ),
      'job_status_history' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'changed_by' => 'users',
        ),
      ),
      'service_approvals' => 
      array (
        'fks' => 
        array (
          'job_service_id' => 'job_services',
          'approved_by' => 'users',
        ),
      ),
      'additional_work_requests' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'requested_by' => 'users',
          'approved_by' => 'users',
        ),
      ),
      'customer_supplied_parts' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'product_id' => 'products',
        ),
      ),
      'emergency_purchases' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'product_id' => 'products',
          'purchased_by' => 'users',
        ),
      ),
      'quality_checks' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'checked_by' => 'users',
        ),
      ),
      'work_time_logs' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'technician_id' => 'users',
        ),
      ),
      'service_bay_assignments' => 
      array (
        'fks' => 
        array (
          'job_id' => 'jobs',
          'service_bay_id' => 'service_bays',
          'assigned_by' => 'users',
        ),
      ),
      'warranties' => 
      array (
        'fks' => 
        array (
          'invoice_id' => 'invoices',
          'product_id' => 'products',
          'job_id' => 'jobs',
        ),
      ),
      'complaints' => 
      array (
        'fks' => 
        array (
          'customer_id' => 'customers',
          'job_id' => 'jobs',
          'assigned_to' => 'users',
          'resolved_by' => 'users',
        ),
      ),
      'warranty_claims' => 
      array (
        'fks' => 
        array (
          'warranty_id' => 'warranties',
          'job_id' => 'jobs',
          'processed_by' => 'users',
        ),
      ),
      'invoices' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
          'branch_id' => 'branches',
          'customer_id' => 'customers',
          'job_id' => 'jobs',
        ),
      ),
      'invoice_items' => 
      array (
        'fks' => 
        array (
          'invoice_id' => 'invoices',
        ),
      ),
      'invoice_versions' => 
      array (
        'fks' => 
        array (
          'invoice_id' => 'invoices',
          'changed_by' => 'users',
        ),
      ),
      'payments' => 
      array (
        'fks' => 
        array (
          'invoice_id' => 'invoices',
          'received_by' => 'users',
        ),
      ),
      'refunds' => 
      array (
        'fks' => 
        array (
          'invoice_id' => 'invoices',
          'payment_id' => 'payments',
          'requested_by' => 'users',
          'approved_by' => 'users',
          'processed_by' => 'users',
        ),
      ),
      'credit_notes' => 
      array (
        'fks' => 
        array (
          'invoice_id' => 'invoices',
          'customer_id' => 'customers',
          'created_by' => 'users',
        ),
      ),
      'discounts' => 
      array (
        'fks' => 
        array (
          'business_id' => 'businesses',
        ),
      ),
      'tills' => 
      array (
        'fks' => 
        array (
        ),
      ),
      'cash_movements' => 
      array (
        'fks' => 
        array (
          'till_id' => 'tills',
          'user_id' => 'users',
        ),
      ),
      'loyalty_accounts' => 
      array (
        'fks' => 
        array (
          'customer_id' => 'customers',
        ),
      ),
      'loyalty_transactions' => 
      array (
        'fks' => 
        array (
          'loyalty_account_id' => 'loyalty_accounts',
          'created_by' => 'users',
        ),
      ),
      'memberships' => 
      array (
        'fks' => 
        array (
          'customer_id' => 'customers',
        ),
      ),
      'service_bays' => 
      array (
        'fks' => 
        array (
          'branch_id' => 'branches',
        ),
      ),
      'equipment' => 
      array (
        'fks' => 
        array (
          'branch_id' => 'branches',
        ),
      ),
      'equipment_maintenance' => 
      array (
        'fks' => 
        array (
          'equipment_id' => 'equipment',
        ),
      ),
      'expenses' => 
      array (
        'fks' => 
        array (
          'branch_id' => 'branches',
          'created_by' => 'users',
          'approved_by' => 'users',
        ),
      ),
      'cash_registers' => 
      array (
        'fks' => 
        array (
          'branch_id' => 'branches',
          'user_id' => 'users',
          'closed_by' => 'users',
        ),
      ),
      'cash_register_transactions' => 
      array (
        'fks' => 
        array (
          'cash_register_id' => 'cash_registers',
        ),
      ),
      'stock_transfers' => 
      array (
        'fks' => 
        array (
          'from_branch_id' => 'branches',
          'to_branch_id' => 'branches',
          'requested_by' => 'users',
          'approved_by' => 'users',
          'received_by' => 'users',
        ),
      ),
      'stock_transfer_items' => 
      array (
        'fks' => 
        array (
          'stock_transfer_id' => 'stock_transfers',
          'product_id' => 'products',
        ),
      ),
      'technician_skills' => 
      array (
        'fks' => 
        array (
          'technician_id' => 'users',
          'service_category_id' => 'service_categories',
        ),
      ),
      'communication_templates' => 
      array (
        'fks' => 
        array (
        ),
      ),
    ),
    'pivots' => 
    array (
      'role_user' => 
      array (
        'fks' => 
        array (
          'role_id' => 'roles',
          'user_id' => 'users',
        ),
      ),
      'permission_role' => 
      array (
        'fks' => 
        array (
          'role_id' => 'roles',
        ),
        'skip_remap' => 
        array (
          0 => 'permission_id',
        ),
      ),
    ),
    'excluded' => 
    array (
      0 => 'audit_logs',
      1 => 'notifications',
      2 => 'notifications_log',
      3 => 'communications',
      4 => 'impersonation_tokens',
      5 => 'sessions',
      6 => 'cache',
      7 => 'cache_locks',
      8 => 'queue_jobs',
      9 => 'job_batches',
      10 => 'failed_jobs',
      11 => 'password_reset_tokens',
    ),
    'polymorphic' => 
    array (
      'inventory_movements' => 
      array (
        'type' => 'reference_type',
        'id' => 'reference_id',
      ),
      'invoice_items' => 
      array (
        'type' => 'item_type',
        'id' => 'item_id',
      ),
      'loyalty_transactions' => 
      array (
        'type' => 'reference_type',
        'id' => 'reference_id',
      ),
    ),
    'polymorphic_aliases' => 
    array (
      'service' => 'services',
      'part' => 'products',
      'product' => 'products',
      'invoice' => 'invoices',
      'job' => 'jobs',
      'loyalty_account' => 'loyalty_accounts',
      'purchase_order' => 'purchase_orders',
      'customer' => 'customers',
    ),
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\resources\\views',
    ),
    'compiled' => 'C:\\WORK\\VELLIX\\AUTOCARE\\old 2\\carwash\\storage\\framework\\views',
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'alias' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
    'trust_project' => 'always',
  ),
);
