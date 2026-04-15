<?php
/*!
 * itop-sso-synology
 *
 * Synology SSO OpenID Connect provider for Hybridauth.
 *
 * @license MIT
 * @link    https://github.com/hybridauth/hybridauth
 */

namespace Hybridauth\Provider;

use Hybridauth\Adapter\OAuth2;
use Hybridauth\Exception\InvalidApplicationCredentialsException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Synology SSO Server (DSM package "SSO Server") OpenID Connect provider.
 *
 * Example config for combodo-hybridauth (iTop user login):
 *
 *   'SynologySSO' => [
 *       'enabled' => true,
 *       'url'     => 'https://sso.example.com',
 *       'keys'    => [
 *           'id'     => 'client-id',
 *           'secret' => 'client-secret',
 *       ],
 *       'scope'          => 'openid email groups',
 *       'allowed_groups' => ['my_sso_group'], // optional
 *   ]
 *
 * Endpoints used (path layout is fixed by Synology SSO Server 3.x):
 *   authorize:  {url}/webman/sso/SSOOauth.cgi
 *   token:      {url}/webman/sso/SSOAccessToken.cgi
 *   userinfo:   {url}/webman/sso/SSOUserInfo.cgi
 *   discovery:  {url}/webman/sso/.well-known/openid-configuration
 *
 * Claims exposed by Synology SSO (v3.0.x):
 *   sub, username, email, groups, iss, aud, iat, exp
 */
class SynologySSO extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'openid email groups';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://kb.synology.com/en-global/DSM/help/SSOServer/sso_server_desc';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        if (!$this->config->exists('url')) {
            throw new InvalidApplicationCredentialsException(
                'You must define a Synology SSO server url (e.g. https://sso.example.com)'
            );
        }
        $url = rtrim($this->config->get('url'), '/');

        $this->apiBaseUrl     = $url . '/webman/sso/';
        $this->authorizeUrl   = $this->apiBaseUrl . 'SSOOauth.cgi';
        $this->accessTokenUrl = $this->apiBaseUrl . 'SSOAccessToken.cgi';

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {
        $response = $this->apiRequest('SSOUserInfo.cgi');

        $data = new Data\Collection($response);

        if (!$data->exists('sub')) {
            throw new UnexpectedApiResponseException(
                'Provider API returned an unexpected response (no sub claim).'
            );
        }

        $userGroups = $data->get('groups');
        if (!is_array($userGroups)) {
            $userGroups = [];
        }

        // Optional group filter: if `allowed_groups` is set in the provider
        // configuration, the authenticated user must belong to at least one
        // of those groups. Used to restrict access to a subset of DSM users.
        $allowedGroups = $this->config->get('allowed_groups');
        if (is_array($allowedGroups) && count($allowedGroups) > 0) {
            $intersection = array_intersect($allowedGroups, $userGroups);
            if (count($intersection) === 0) {
                throw new UnexpectedApiResponseException(
                    'Access denied: user does not belong to any of the allowed groups ('
                    . implode(', ', $allowedGroups) . ').'
                );
            }
        }

        $userProfile = new User\Profile();
        $userProfile->identifier  = $data->get('sub');
        $userProfile->email       = $data->get('email');
        $userProfile->displayName = $data->get('username');

        // Propagate groups claim for downstream processing.
        if (count($userGroups) > 0) {
            $userProfile->data['groups'] = $userGroups;
        }

        return $userProfile;
    }
}
