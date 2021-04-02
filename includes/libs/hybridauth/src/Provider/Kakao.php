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

class Kakao extends OAuth2
{
    /**
    * {@inheritdoc}
    */
    // public $scope = 'https://www.googleapis.com/auth/userinfo.profile https://www.googleapis.com/auth/userinfo.email';

    /**
    * {@inheritdoc}
    */
    protected $apiBaseUrl = 'https://kapi.kakao.com/';
    // protected $apiBaseUrl = 'https://kauth.kakao.com/';

    /**
    * {@inheritdoc}
    */
    protected $authorizeUrl = 'https://kauth.kakao.com/oauth/authorize';

    /**
    * {@inheritdoc}
    */
    protected $accessTokenUrl = 'https://kauth.kakao.com/oauth/token';

    /**
    * {@inheritdoc}
    */
    protected $apiDocumentation = 'https://developers.google.com/identity/protocols/OAuth2';


    protected function initialize()
    {
      parent::initialize();

      // The Instagram API requires an access_token from authenticated users
      // for each endpoint, see https://www.instagram.com/developer/endpoints.
      $accessToken = $this->getStoredData('access_token');

      $this->apiRequestParameters['access_token'] = str_replace( "#_", "", $accessToken );
      $this->apiRequestParameters['client_id'] = $this->clientId;
      $this->apiRequestParameters['app_secret'] = $this->clientSecret;

      $this->AuthorizeUrlParameters['client_id'] = $this->clientId;

      $this->tokenExchangeParameters['client_id'] = $this->clientId;
      $this->tokenExchangeParameters['app_secret'] = $this->clientSecret;

      // $this->tokenExchangeParameters['redirect_uri'] = remove_query_arg("provider", $this->tokenExchangeParameters['redirect_uri'] );

      // unset( $this->tokenExchangeParameters['client_id'] );
      // unset( $this->tokenExchangeParameters['app_secret'] );
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

        $response = $this->apiRequest('v2/user/me', 'GET', $parameters );
        $data = new Data\Collection($response);

        if (! $data->exists('id')) {
            throw new UnexpectedApiResponseException('Provider API returned an unexpected response.');
        }

        $userProfile = new User\Profile();
        $kakao_properties = $data->get('properties');

        $userProfile->identifier  = $data->get('id');
        $userProfile->displayName = $data->filter('properties')->get('nickname');
        $avatar_uri = $data->filter('properties')->get('profile_image');
        if(empty($avatar_uri) || ! filter_Var($avatar_uri, FILTER_VALIDATE_URL) ){
          $avatar_uri = um_get_default_avatar_uri();
        }

        $userProfile->photoURL    = $avatar_uri;
        $userProfile->email       = $data->filter('kakao_account')->get('email');
        $userProfile->gender      = $this->fetchGender($data->filter('kakao_account')->get('gender'));
        $userProfile->firstName   = $data->filter('kakao_account')->filter('profile')->get('nickname');
        // $userProfile->lastName    = '';
        $userProfile->displayName = $userProfile->displayName ?: $data->get('username');
        $userProfile->emailVerified = $userProfile->email;
        $userProfile->birthYear     = $data->filter('kakao_account')->get('birthyear');
        $userProfile->phone         = $data->filter('kakao_account')->get('phone_number');
        $userProfile = $this->fetchBirthday($userProfile, $data->filter('kakao_account')->get('birthday') , $data->filter('kakao_account')->get('age_range'));
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
        $result = str_split($birthday, 2);
        $userProfile->birthMonth = (int)$result[0];
        $userProfile->birthDay = (int)$result[1];

        if( !empty($userProfile->birthYear ) && !empty( $birthday )){
          $birth_date       = $userProfile->birthYear . "-" . $result[0] . "-" . $result[1];
          $birth_time       = strtotime($birth_date);
          $now              = date('Y');
          $birthday         = date('Y' , $birth_time);
          $userProfile->age = $now - $birthday + 1 ;
        }elseif (!empty($userProfile->birthYear ) ) {
          $userProfile->age = date(Y) - (int)$userProfile->birthYear + 1 ;
        }elseif ( !empty($age_range) ) {
          $age_range = explode('~' , $age_range);
          $userProfile->age = $age_range[0].'대';
        }else{
          $userProfile->age = '';
        }

        return $userProfile;
    }

    protected function fetchGender($gender)
    {
      if( !empty($gender) ){
        $gender = $gender == 'female' ? '여성' : '남성';
      }
      return $gender;
    }

    public function expires_token()
    {
      $parameters = [
        'fields' => 'application/json',
        'Authorization' => 'Bearer '. $this->getStoredData( $this->accessTokenName ),
      ];

      $response = $this->apiRequest('v1/user/logout', 'GET', $parameters );
    }
}
