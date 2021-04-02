<?php
/**
 * Copyright (c) 2014 Team TamedBitches.
 * Written by Chuck JS. Oh <jinseokoh@hotmail.com>
 * http://facebook.com/chuckoh
 *
 * Date: 11 10, 2014
 * Time: 01:51 AM
 *
 * This program is free software. It comes without any warranty, to
 * the extent permitted by applicable law. You can redistribute it
 * and/or modify it under the terms of the Do What The Fuck You Want
 * To Public License, Version 2, as published by Sam Hocevar. See
 * http://www.wtfpl.net/txt/copying/ for more details.
 *
 */

namespace Hybridauth\Provider;

use Hybridauth\Exception\InvalidArgumentException;
use Hybridauth\Exception\UnexpectedApiResponseException;
use Hybridauth\Adapter\OAuth2;
use Hybridauth\Data;
use Hybridauth\User;

class Naver extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    // public $scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://openapi.naver.com/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://nid.naver.com/oauth2.0/authorize';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://nid.naver.com/oauth2.0/token';

    /**
    * {@inheritdoc}
    */
    // protected $apiDocumentation = 'https://developers.google.com/identity/protocols/OAuth2';


    protected function initialize()
    {
      parent::initialize();

      $this->tokenRefreshParameters = [
          'grant_type'    => 'refresh_token',
          'client_id'     => $this->clientId,
          'client_secret' => $this->clientSecret,
          'refresh_token' => $this->getStoredData('refresh_token'),
      ];

      $accessToken = $this->getStoredData('access_token');

      $this->apiRequestParameters['access_token'] = str_replace( "#_", "", $accessToken );
      $this->apiRequestParameters['client_id'] = $this->clientId;
      $this->apiRequestParameters['client_secret'] = $this->clientSecret;
      // $this->apiRequestParameters['state'] = $this->clientSecret;

      $this->AuthorizeUrlParameters['client_id'] = $this->clientId;

      $this->tokenExchangeParameters['client_id'] = $this->clientId;
      $this->tokenExchangeParameters['client_secret'] = $this->clientSecret;

      $this->tokenExchangeParameters['redirect_uri'] = remove_query_arg("provider", $this->tokenExchangeParameters['redirect_uri'] );

      // unset( $this->tokenExchangeParameters['client_id'] );
      // unset( $this->tokenExchangeParameters['client_secret'] );
      //
      // unset( $this->apiRequestParameters['client_id'] );
      // unset( $this->apiRequestParameters['client_secret'] );
      //
      // unset( $this->AuthorizeUrlParameters['client_id'] );
      // unset( $this->AuthorizeUrlParameters['client_secret'] );
    }

    /**
    * {@inheritdoc}
    *
    * See: https://developers.google.com/identity/protocols/OpenIDConnect#obtainuserinfo
    */
    public function getUserProfile()
    {
        $accessToken = $this->getStoredData( $this->accessTokenName );
        if(empty($this->getStoredData( $this->accessTokenName ))){
          return '';
        }
        $parameters = [
          'fields' => 'application/json',
          'Authorization' => 'Bearer '. $accessToken,
        ];

        $response = $this->apiRequest('v1/nid/me', 'GET', $parameters );
        $data = new Data\Collection($response);
        if (! $data->exists('response')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();

        $userProfile->identifier  = $data->filter('response')->get('id');
        $userProfile->displayName = $data->filter('response')->get('nickname');

        $avatar_uri = $data->filter('response')->get('profile_image');
        if(empty($avatar_uri) || filter_Var($avatar_uri, FILTER_VALIDATE_URL) ){
          $avatar_uri = um_get_default_avatar_uri();
        }


        $userProfile->photoURL      = $avatar_uri;
        $userProfile->email         = $data->filter('response')->get('email');
        $userProfile->emailVerified = $data->filter('response')->get('email');
        $userProfile->firstName     = $data->filter('response')->get('name');
        $userProfile->displayName   = $data->filter('response')->get('nickname');
        $userProfile->birthYear     = $data->filter('response')->get('birthyear');
        $userProfile->phone         = $data->filter('response')->get('mobile');
        $userProfile->gender        = $this->fetchGender( $data->filter('response')->get('gender') );
        $userProfile = $this->fetchBirthday($userProfile, $data->filter('response')->get('birthday') , $data->filter('kakao_account')->get('age'));

        return $userProfile;
    }

    /**
     * Retrieve the user birthday.
     *
     * @param User\Profile $userProfile
     * @param string $birthday
     *
     * @return \Hybridauth\User\Profile
     */
    protected function fetchBirthday(User\Profile $userProfile, $birthday , $age_range)
    {
        if( !empty( $birthday ) ){
          $result = explode( '-' , $birthday);
          $userProfile->birthMonth = (int)$result[0];
          $userProfile->birthDay = (int)$result[1];
        }

        if( !empty($userProfile->birthYear ) && !empty( $birthday )){
          $birth_date       = $userProfile->birthYear . "-" . $result[0] . "-" . $result[1];
          $birth_time       = strtotime($birth_date);
          $now              = date('Y');
          $birthday         = date('Y' , $birth_time);
          $userProfile->age = $now - $birthday + 1 ;
        }elseif (!empty($userProfile->birthYear ) ) {
          $userProfile->age = date(Y) - (int)$userProfile->birthYear + 1 ;
        }elseif ( !empty($age_range) ) {
          $age_range = explode('-' , $age_range);
          $userProfile->age = $age_range[0].'대';
        }else{
          $userProfile->age = '';
        }

        return $userProfile;
    }

    protected function fetchGender($gender)
    {
      switch ($gender) {
        case 'F':
          $gender = '여성';
          break;
        case 'M':
          $gender = '남성';
          break;
        default:
          $gender = '';
          break;
      }
      return $gender;
    }

    public function expires_token()
    {
      // $parameters = [
      //     'grant_type'    => 'delete',
      //     'client_id'     => $this->clientId,
      //     'client_secret' => $this->clientSecret,
      //     'access_token'  => $this->getStoredData( $this->accessTokenName ),
      //     'service_provider'  => 'NAVER',
      // ];

      // $response = $this->deleteAccessToken( $parameters );
    }
}
