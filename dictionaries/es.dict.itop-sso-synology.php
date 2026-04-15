<?php
/**
 * itop-sso-synology - Spanish dictionary (ES CR).
 *
 * @license MIT
 */

Dict::Add('ES CR', 'Spanish', 'Español', [
    'Class:SynologySSOOauth2Client'                => 'Synology SSO OAuth 2.0 client',
    'Class:SynologySSOOauth2Client+'               => 'Cliente OAuth 2.0 / OpenID Connect para Synology SSO Server (paquete DSM).',
    'Class:SynologySSOOauth2Client/Attribute:url'  => 'URL',
    'Class:SynologySSOOauth2Client/Attribute:url+' => 'URL base del Synology SSO Server (ej. https://sso.ejemplo.com). Los endpoints /webman/sso/ se añaden automáticamente.',
]);
