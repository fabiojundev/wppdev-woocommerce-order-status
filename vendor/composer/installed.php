<?php return array(
    'root' => array(
        'name' => 'wpplugindev/woocommerce-order-workflow',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => '3eea98fb52be20184088b40dd25da3672a846982',
        'type' => 'library',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'monolog/monolog' => array(
            'pretty_version' => '2.8.0',
            'version' => '2.8.0.0',
            'reference' => '720488632c590286b88b80e62aa3d3d551ad4a50',
            'type' => 'library',
            'install_path' => __DIR__ . '/../monolog/monolog',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log' => array(
            'pretty_version' => '1.1.4',
            'version' => '1.1.4.0',
            'reference' => 'd49695b909c3b7628b6289db5479a1c204601f11',
            'type' => 'library',
            'install_path' => __DIR__ . '/../psr/log',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'psr/log-implementation' => array(
            'dev_requirement' => false,
            'provided' => array(
                0 => '1.0.0 || 2.0.0 || 3.0.0',
            ),
        ),
        'wpplugindev/framework' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '6166399269e98c1b776abef89088ae46c8b2e56f',
            'type' => 'library',
            'install_path' => __DIR__ . '/../wpplugindev/framework',
            'aliases' => array(
                0 => '9999999-dev',
            ),
            'dev_requirement' => false,
        ),
        'wpplugindev/woocommerce-order-workflow' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => '3eea98fb52be20184088b40dd25da3672a846982',
            'type' => 'library',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
    ),
);
