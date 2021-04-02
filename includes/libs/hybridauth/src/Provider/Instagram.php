<?php
/*!
* Hybridauth
* https://hybridauth.github.io | https://github.com/hybridauth/hybridauth
*  (c) 2017 Hybridauth authors | https://hybridauth.github.io/license.html
*/

namespace Hybridauth\Provider;

use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Data;
use Hybridauth\User;

/**
 * Instagram OAuth2 provider adapter.
 */
class Instagram extends OAuth2
{
    /**
     * {@inheritdoc}
     */
    protected $scope = 'user_profile,user_media';

    /**
     * {@inheritdoc}
     */
    protected $apiBaseUrl = 'https://graph.instagram.com/';

    /**
     * {@inheritdoc}
     */
    protected $authorizeUrl = 'https://api.instagram.com/oauth/authorize/';

    /**
     * {@inheritdoc}
     */
    protected $accessTokenUrl = 'https://api.instagram.com/oauth/access_token/';

    /**
     * {@inheritdoc}
     */
    protected $apiDocumentation = 'https://www.instagram.com/developer/authentication/';

    /**
     * {@inheritdoc}
     */
    protected function initialize()
    {
        parent::initialize();

        // The Instagram API requires an access_token from authenticated users
        // for each endpoint, see https://www.instagram.com/developer/endpoints.
        $accessToken = $this->getStoredData('access_token');
        $this->apiRequestParameters['access_token'] = str_replace( "#_", "", $accessToken );
        $this->apiRequestParameters['app_id'] = $this->clientId;
        $this->apiRequestParameters['app_secret'] = $this->clientSecret;
        
        $this->AuthorizeUrlParameters['app_id'] = $this->clientId;

        $this->tokenExchangeParameters['app_id'] = $this->clientId;
        $this->tokenExchangeParameters['app_secret'] = $this->clientSecret;

        $this->tokenExchangeParameters['redirect_uri'] = remove_query_arg("provider", $this->tokenExchangeParameters['redirect_uri'] );
       
        
        unset( $this->tokenExchangeParameters['client_id'] );
        unset( $this->tokenExchangeParameters['client_secret'] );

        unset( $this->apiRequestParameters['client_id'] );
        unset( $this->apiRequestParameters['client_secret'] );
        
        unset( $this->AuthorizeUrlParameters['client_id'] );
        unset( $this->AuthorizeUrlParameters['client_secret'] );
       
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile()
    {

        $parameters = [
            'fields' => 'id,username,account_type,media_count',
            $this->accessTokenName => $this->getStoredData( $this->accessTokenName ),
        ];

        $response = $this->apiRequest('me', 'GET', $parameters );
        
        $data = new Data\Collection($response);

        if (! $data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->get('id');
        $userProfile->displayName = $data->get('username');
        $userProfile->profileURL = "https://instagram.com/{$data->get('username')}";
        $userProfile->data = array(
            'account_type' => $data->get('account_type'),
            'media_count' => $data->get('media_count'),
        );

        return $userProfile;
    }
}
