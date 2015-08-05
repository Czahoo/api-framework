<?php
/**
 * Tablica w formie 'nazwa_modulu' => 'nazwa_klasy_z_namespacem',
 */
$FRAMEWORK_ROUTING = array(
    Framework::API_TYPE_EXTERNAL => array(
        Framework::DEFAULT_CONTROLLER_ROUTE_NAME => 'App\Basic\Controller\BasicController',
    ),
    Framework::API_TYPE_INTERNAL => array(
        Framework::DEFAULT_CONTROLLER_ROUTE_NAME => 'App\Basic\Controller\BasicController',
    ),
);