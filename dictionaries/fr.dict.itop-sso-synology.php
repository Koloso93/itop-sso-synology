<?php
/**
 * itop-sso-synology - French dictionary (FR FR).
 *
 * @license MIT
 */

Dict::Add('FR FR', 'French', 'Français', [
    'Class:SynologySSOOauth2Client'                => 'Synology SSO OAuth 2.0 client',
    'Class:SynologySSOOauth2Client+'               => 'Client OAuth 2.0 / OpenID Connect pour Synology SSO Server (paquet DSM).',
    'Class:SynologySSOOauth2Client/Attribute:url'  => 'URL',
    'Class:SynologySSOOauth2Client/Attribute:url+' => 'URL de base du serveur Synology SSO (par ex. https://sso.exemple.com). Les endpoints /webman/sso/ sont ajoutés automatiquement.',
]);
