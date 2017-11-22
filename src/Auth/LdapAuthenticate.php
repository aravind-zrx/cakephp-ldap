<?php
namespace LdapUtility\Auth;

use Cake\Auth\BaseAuthenticate;
use Cake\Controller\ComponentRegistry;
use Cake\Network\Request;
use Cake\Network\Response;
use Cake\ORM\TableRegistry;
use LdapUtility\Exception\LdapException;
use LdapUtility\Ldap;

/**
 * LDAP authentication adapter for AuthComponent
 *
 * Provides LDAP authentication for given username and password
 *
 * ## usage
 * Add LDAP auth to controllers component
 */
class LdapAuthenticate extends BaseAuthenticate
{
    protected $ldap = null;

    /**
     * Constructor
     *
     * {@inheritDoc}
     */
    public function __construct(ComponentRegistry $registry, array $config = [])
    {
        parent::__construct($registry, $config);

        $this->ldap = new ldap($config);
    }

    /**
     * Authenticate user
     *
     * {@inheritDoc}
     */
    public function authenticate(Request $request, Response $response)
    {
        if (empty($request->data['username']) || empty($request->data['password'])) {
            throw new LdapException('Empty username or password');
        }

        return $this->_findUser($request->data['username'], $request->data['password']);
    }

    /**
     * Find user method
     *
     * @param string $username Username
     * @param string $password Password
     * @return bool|array
     */
    protected function _findUser($username, $password = null)
    {
        $ldapUserDetails = $this->ldap->authenticateUser($username, $password);

        if (!$ldapUserDetails || empty($ldapUserDetails[0]['mail'][0])) {
            return false;
        }

        if (!empty($ldapUserDetails['role_suffix'])) {
            $userEmail = $this->ldap->addSuffixToMailbox($ldapUserDetails[0]['mail'][0], $ldapUserDetails['role_suffix']);
        } else {
            $userEmail = $ldapUserDetails[0]['mail'][0];
        }

        $user = parent::_findUser($userEmail);
        $callback = $this->_config['auth']['callback'] ?? '';
        if (!empty($callback)) {
            $user = TableRegistry::get($this->_config['userModel'])->$callback($ldapUserDetails, $user);
        }
        if (!empty($user)) {
            $user['ldap_cn'] = $ldapUserDetails[0]['cn'][0];
        }

        return $user;
    }

    /**
     * Destructor
     * Close LDAP connection if any
     *
     * @return void
     */
    public function __destruct()
    {
        if (!empty($this->ldap) && method_exists($this->ldap, 'close')) {
            $this->ldap->close();
        }
    }
}
