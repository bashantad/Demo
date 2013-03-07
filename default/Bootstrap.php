<?php

class Default_Bootstrap extends Zend_Application_Module_Bootstrap
{

    protected function _initAttributeExOpenIDPath()
    {
        $autoLoader = Zend_Loader_Autoloader::getInstance();

        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
                    'basePath' => LAYOUT_BASEPATH . '/default',
                    'namespace' => 'My_',
                ));

        $resourceLoader->addResourceType('openidextension', 'openid/extension/', 'OpenId_Extension');
        $resourceLoader->addResourceType('authAdapter', 'auth/adapter', 'Auth_Adapter');

        $autoLoader->pushAutoloader($resourceLoader);
    }

    protected function _initAppKeysToRegistry()
    {
        $appkeys = new Zend_Config_Ini(APPLICATION_PATH . '/configs/appkeys.ini');
        Zend_Registry::set('keys', $appkeys);
    }
    public function initRouting()
    {
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router->removeDefaultRoutes();
        
        $routeDefaultLanguage = new Zend_Controller_Router_Route(
                        '/:lang/:controller/:action/*',
                        array('lang' => 'en')
        );
        $router->addRoute("routedefaultlanguage",$routeDefaultLanguage);
        $routeDefaultModule = new Zend_Controller_Router_Route(
                        '/:lang/:controller/',
                        array('action' => 'index','module'=>'default')
        );
        $router->addRoute("routedefaultmodule",$routeDefaultModule);
        $routeDefaultController = new Zend_Controller_Router_Route(
                        '/:controller/',
                        array('lang' => 'en','module'=>'default','action'=>'index')
        );
        $router->addRoute("routedefaultcontroller", $routeDefaultController);
        $routeDefaultControllerAction = new Zend_Controller_Router_Route(
                        '/',
                        array('lang' => 'en','module'=>'default','controller'=>'index','action'=>'index')
        );
        $router->addRoute("routeDefaultControllerAction", $routeDefaultControllerAction);
    }

}


