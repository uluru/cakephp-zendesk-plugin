<?php
/*
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                  #   #   #     #   #   ####    #   #
                  #   #   #     #   #   #   #   #   #
                   ###    ####   ###    #   #    ###

             Copyright 2014 ULURU.CO.,LTD. All Rights Reserved.

*/

App::import('Vendor', 'autoload');
use Firebase\JWT\JWT as JWT;

/**
 * sso Controller
 *
 * @package     zendesk
 * @subpackage  Controller
 */
class SsoController extends ZendeskAppController
{
    public $name = 'Sso';
    public $uses = null;

    public $components = [
        'Session',
        'Auth',
    ];

    private $defaultAuthKeys = [
        'name'             => 'username',
        'email'            => 'email',
        'external_id'      => 'id',
        'remote_photo_url' => 'thumbnail_url',
    ];

    public function beforeFilter()
    {
        parent::beforeFilter();
        $this->Auth->allow();
    }

    public function index()
    {
        if (is_null($this->Auth->user())) {
            $this->Session->write('Auth.redirect', env('REQUEST_URI'));
            $this->redirect(am(['plugin' => false], Configure::read('Zendesk.loginUrl')));
        }

        $token = [
            'jti' => Security::hash(String::uuid(), null, true),
            'iat' => time(),
        ];

        $authKeys = am($this->defaultAuthKeys, Configure::read('Zendesk.authKeys'));
        foreach ($authKeys as $tokenKey => $authKey) {
            $token[$tokenKey] = $this->Auth->user($authKey);
            if (empty($token[$tokenKey])) {
                unset($token[$tokenKey]);
            }
        }

        $jwt = JWT::encode($token, Configure::read('Zendesk.sharedKey'));

        // Redirect
        $url = sprintf(
            "%s/access/jwt?jwt=%s",
            Configure::read('Zendesk.url'),
            $jwt
        );

        $this->redirect($url);
    }
}

/* vim: set et ts=4 sts=4 sw=4 fenc=utf-8 ff=unix : */
