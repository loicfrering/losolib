<?php

class ApplicationServiceContainer extends sfServiceContainer
{
  protected $shared = array();

  public function __construct()
  {
    parent::__construct($this->getDefaultParameters());
  }

  protected function getTestServiceService()
  {
    if (isset($this->shared['testService'])) return $this->shared['testService'];

    $instance = new Application_Service_TestService();
    $instance->setFoo($this->getParameter('foo'));
    $instance->setTestService2($this->getService('testService2'));

    return $this->shared['testService'] = $instance;
  }

  protected function getTestService2Service()
  {
    if (isset($this->shared['testService2'])) return $this->shared['testService2'];

    $instance = new Application_Service_TestService2();

    return $this->shared['testService2'] = $instance;
  }

  protected function getDefaultParameters()
  {
    return array(
      'foo' => 'bar',
      'fooarray' => array(
        0 => true,
        1 => false,
        2 => 786,
        3 => 'value',
      ),
    );
  }
}
