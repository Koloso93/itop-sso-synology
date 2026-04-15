<?php
/**
 * itop-sso-synology - iTop module descriptor.
 *
 * Adds "Synology SSO OAuth 2.0 client" as a client type for the
 * combodo-oauth2-client iTop extension, and provides a Hybridauth
 * provider that can also be used by combodo-hybridauth for user
 * login against a Synology SSO Server (DSM package).
 *
 * @license MIT
 */

SetupWebPage::AddModule(
    __FILE__,
    'itop-sso-synology/1.0.0',
    [
        'label'        => 'Synology SSO OAuth 2.0 client',
        'category'     => 'business',
        'dependencies' => [
            'combodo-oauth2-client/1.0.0',
        ],
        'mandatory'    => false,
        'visible'      => true,
        'datamodel'    => [
            'datamodel.itop-sso-synology.xml',
            'src/autoload.php',
        ],
        'data.struct'          => [],
        'data.sample'          => [],
        'doc.manual_setup'     => '',
        'doc.more_information' => '',
        'settings'             => [],
    ]
);
