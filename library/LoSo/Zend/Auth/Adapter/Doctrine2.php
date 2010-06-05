<?php
class LoSo_Zend_Auth_Adapter_Doctrine2 implements Zend_Auth_Adapter_Interface
{
    protected $em;
    protected $entityName;
    protected $identityField;
    protected $credentialField;

    public function __construct($em, $entityName = null, $identityField = null, $credentialField = null)
    {
        $this->em = $em;

        if (null !== $entityName) {
            $this->setEntityName($entityName);
        }

        if (null !== $identityField) {
            $this->setIdentityField($identityField);
        }

        if (null !== $credentialField) {
            $this->setCredentialField($credentialField);
        }
    }

    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
        return $this;
    }

    public function setIdentityField($identityField)
    {
        $this->identityField = $identityField;
        return $this;
    }

    public function setCredentialField($credentialField)
    {
        $this->credentialField = $credentialField;
        return $this;
    }

    public function setIdentity($identity)
    {
        $this->identity = $identity;
        return $this;
    }

    public function setCredential($credential)
    {
        $this->credential = $credential;
        return $this;
    }

    public function authenticate()
    {
        $this->_authenticateSetup();
        $query = $this->_getQuery();

        $authResult = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => null,
            'messages' => array()
        );

        try {
            $result = $query->execute(array(1 => $this->identity));

            $resultCount = count($result);
            if ($resultCount > 1) {
                $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
                $authResult['messages'][] = 'More than one entity matches the supplied identity.';
            } else if ($resultCount < 1) {
                $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
                $authResult['messages'][] = 'A record with the supplied identity could not be found.';
            } else if (1 == $resultCount) {
                if ($result[0][$this->credentialField] != $this->credential) {
                    $authResult['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
                    $authResult['messages'][] = 'Supplied credential is invalid.';
                } else {
                    $authResult['code'] = Zend_Auth_Result::SUCCESS;
                    $authResult['identity'] = $this->identity;
                    $authResult['messages'][] = 'Authentication successful.';
                }
            }
        } catch (\Doctrine\ORM\Query\QueryException $qe) {
            $authResult['code'] = Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $authResult['messages'][] = $qe->getMessage();
        }

        return new Zend_Auth_Result(
            $authResult['code'],
            $authResult['identity'],
            $authResult['messages']
        );
    }

    /**
     * _authenticateSetup() - This method abstracts the steps involved with
     * making sure that this adapter was indeed setup properly with all
     * required pieces of information.
     *
     * @throws Zend_Auth_Adapter_Exception - in the event that setup was not done properly
     */
    protected function _authenticateSetup()
    {
        $exception = null;

        if (null === $this->em || !$this->em instanceof \Doctrine\ORM\EntityManager) {
            $exception = 'A Doctrine2 EntityManager must be supplied for the Zend_Auth_Adapter_Doctrine2 authentication adapter.';
        } elseif (empty($this->identityField)) {
            $exception = 'An identity field must be supplied for the Zend_Auth_Adapter_Doctrine2 authentication adapter.';
        } elseif (empty($this->credentialField)) {
            $exception = 'A credential field must be supplied for the Zend_Auth_Adapter_Doctrine2 authentication adapter.';
        } elseif (empty($this->identity)) {
            $exception = 'A value for the identity was not provided prior to authentication with Zend_Auth_Adapter_Doctrine2.';
        } elseif (empty($this->credential)) {
            $exception = 'A credential value was not provided prior to authentication with Zend_Auth_Adapter_Doctrine2.';
        }

        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }
    }

    protected function _getQuery()
    {
        $qb = $this->em->createQueryBuilder()
            ->select('e.' . $this->credentialField . ', e')
            ->from($this->entityName, 'e')
            ->where('e.' . $this->identityField . ' = ?1');

        return $qb->getQuery();
    }
}
