<?php
use LoSo\LosoBundle\DependencyInjection\Annotations as DI;

/**
 * Description of Application_Service_TestService2
 *
 * @author Loïc Frering <loic.frering@gmail.com>
 * @DI\Service
 */
class Application_Service_TestService2
{
    public function test2()
    {
        return 'Test2 method from TestService2';
    }
}
