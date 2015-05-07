<?php

namespace Application\Auth;

use Bluz\Proxy\Config;
use Bluz\Proxy\Messages;
use Application\Auth;
use Application\Users;

class AuthProvider implements AuthInterface
{
    /** @var  \Bluz\Http\Response */
    protected $response;

    /** @var \Application\Users\Row $identity */
    protected $identity;

    /** @var \Hybrid_Auth $hybridauth */
    protected $hybridauth;

    /** @var \Hybrid_Provider_Adapter $authAdapter */
    protected $authAdapter;

    /**
     * the same name as was mentioned in hybridauth config section providers
     * @var string
     */
    protected $providerName;

    public function __construct($providerName)
    {
        if (!in_array(ucfirst($providerName), $this->getAvailableProviders())) {

            throw new \Exception(sprintf('Provider % is not defined in configuration file', ucfirst($providerName)));
        }
        $this->providerName = ucfirst($providerName);
    }


    /**
     * @return \Hybrid_Auth
     */
    public function getHybridauth()
    {
        if (!$this->hybridauth) {
            $this->hybridauth = new \Hybrid_Auth($this->getOptions());
        }
        return $this->hybridauth;
    }

    public function setHybridauth($hybridauth)
    {
        $this->hybridauth = $hybridauth;
    }


    /**
     * @param \Bluz\Http\Response $response
     */
    public function setResponse($response)
    {
        $this->response = $response;
    }

    /**
     * @return \Bluz\Http\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param \Application\Users\Row $identity
     */
    public function setIdentity($identity)
    {
        $this->identity = $identity;
    }

    /**
     * @return \Application\Users\Row $user
     */
    public function getIdentity()
    {
        return $this->identity;
    }

    /**
     * @return string
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * @param string $providerName
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * @return \Hybrid_Provider_Adapter
     * @throws \Exception
     */
    public function getAuthAdapter()
    {
        if (!$this->authAdapter) {

            /** @var \Hybrid_Provider_Adapter $authProvider */
            $this->authAdapter = $this->getHybridauth()->authenticate($this->providerName);

            if (!$this->authAdapter->isUserConnected()) {
                throw new \Exception('Cannot connect to current provider !');
            }

        }
        return $this->authAdapter;
    }

    /**
     * @param \Hybrid_Provider_Adapter $authAdapter
     */
    public function setAuthAdapter($authAdapter)
    {
        $this->authAdapter = $authAdapter;
    }


    /**
     * @param \Hybrid_User_Profile $data
     * @param  \Application\Users\Row $user
     * @return void
     */
    public function registration($data, $user)
    {
        $twitterRow = new Auth\Row();
        $twitterRow->userId = $user->id;
        $twitterRow->provider = strtolower($this->providerName);

        $twitterRow->foreignKey = $data->identifier;
        $twitterRow->token = $this->authAdapter->getAccessToken()['access_token'];
        $twitterRow->tokenSecret = ($this->authAdapter->getAccessToken()['access_token_secret']) ?
            $this->authAdapter->getAccessToken()['access_token_secret'] : '';
        $twitterRow->tokenType = Auth\Table::TYPE_ACCESS;
        $twitterRow->save();

        Messages::addNotice(sprintf('Your account was linked to %s successfully !', ucfirst($this->providerName)));
        $this->response->redirectTo('users', 'profile', ['id' => $user->id]);
    }


    /**
     * @return void
     */
    public function authProcess()
    {
        $this->authAdapter = $this->getAuthAdapter();
        $profile = $this->getProfile();

        $auth = Auth\Table::findRowWhere([strtolower($this->providerName)=> $profile->identifier]);

        /**
         * @var Auth\Table $authTable
         */
       // $authTable = Auth\Table::getInstance();
       // $auth = $authTable->getAuthRow(strtolower($this->providerName), $profile->identifier);

        if ($this->identity) {
            if ($auth) {
                Messages::addNotice(sprintf('You have already linked to %s', ucfirst($this->providerName)));
                $this->response->redirectTo('users', 'profile', ['id' => $this->identity->id]);
            } else {
                $user = Users\Table::findRow($this->identity->id);
                $this->registration($profile, $user);
            }
        }

        if ($auth) {
            $this->alreadyRegisteredLogic($auth);
        } else {
            Messages::addError(sprintf('First you need to be linked to %s', ucfirst($this->providerName)));
            $this->response->redirectTo('users', 'signin');
        }
    }

    /**
     * @return array
     * @throws \Application\Exception
     */
    public function getOptions()
    {
        return Config::getData('hybridauth');
    }

    /**
     * @return array
     */
    public function getAvailableProviders()
    {
        return array_keys(Config::getData('hybridauth')['providers']);
    }

    /**
     * @param $auth
     * @return mixed
     */
    public function alreadyRegisteredLogic($auth)
    {
        $user = Users\Table::findRow($auth->userId);

        if ($user->status != Users\Table::STATUS_ACTIVE) {
            Messages::addError('User is not active');
        }

        $user->login();
        $this->response->redirectTo('index', 'index');
    }

    /**
     * @return \Hybrid_User_Profile
     */
    public function getProfile()
    {
        return $this->authAdapter->getUserProfile();
    }

}