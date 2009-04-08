<?php

require_once SCRIPT_BASE.'roxlauncher/roxloader.php';
require_once 'environmentexplorer.php';

/**
 * methods in here get called by other methods in PTLauncher.
 *
 */
class RoxLauncher
{
    /**
     * central starting point.
     * to be called in htdocs/index.php
     */
    function launch()
    {
        $env_explore = $this->initializeGlobalState();
        
        try {
            // find an app and run it.
            $this->chooseAndRunApplication($env_explore);
        } catch (Exception $e) {
            if (class_exists('ExceptionPage')) {
                $page = new ExceptionPage;
                $page->exception = $e;
                $page->render();
            } else {
                echo '
                <h2>A terrible '.get_class($e).' was thrown</h2>
                <p>RoxLauncher is feeling sorry.</p>
                <pre>';
                print_r($e);
                echo '
                </pre>';
            }
        }
    }
    
    
    /**
     * choose a controller and call the index() function.
     * If necessary, flush the buffered output.
     */
    protected function chooseAndRunApplication($env_explore)
    {
        $args = new stdClass;
        $args->request_uri = $_SERVER['REQUEST_URI'];
        $args->request = PRequest::get()->request;
        $args->req = implode('/', $args->request);
        $args->get = $_GET;
        $args->post = $_POST;
        $args->get_or_post = array_merge($_POST, $_GET);
        $args->post_or_get = array_merge($_GET, $_POST);
        
        $router = new RoxFrontRouter();
        $router->classes = $env_explore->classes;
        $router->env = $env_explore;
        $router->session_memory = new SessionMemory('SessionMemory');
        
        $router->route($args);
    }
    
    
    /**
     * This is called from
     * htdocs/bw/lib/tbinit.php
     */
    function initBW()
    {
        $this->initializeGlobalState();
    }
    
    
    protected function initializeGlobalState()
    {
        $env_explore = new EnvironmentExplorer;
        $env_explore->initializeGlobalState();
        return $env_explore;
    }
}




?>