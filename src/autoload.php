<?php
/**
 * itop-sso-synology - autoloader.
 *
 * Registers the Hybridauth\Provider\SynologySSO class so that it becomes
 * resolvable from any module using the Hybridauth library (notably
 * combodo-hybridauth for user login and combodo-oauth2-client for
 * server-to-server calls).
 *
 * This autoloader is self-contained: it does not modify the classmap of
 * combodo-oauth2-client (which is classmap-authoritative and would not
 * pick new classes up without regeneration), and it is not affected by
 * combodo-oauth2-client upgrades.
 *
 * @license MIT
 */

spl_autoload_register(function ($class) {
    $map = [
        'Hybridauth\\Provider\\SynologySSO' => __DIR__ . '/Provider/SynologySSO.php',
    ];
    if (isset($map[$class]) && is_file($map[$class])) {
        require_once $map[$class];
    }
});
