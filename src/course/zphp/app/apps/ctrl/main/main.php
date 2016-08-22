<?php
namespace ctrl\main;
use ZPHP\Controller\IController,
    ZPHP\Core\Config,
    ZPHP\View;

class main implements IController
{

    public function _before()
    {
        return true;
    }

    public function _after()
    {
        //
    }

    public function main()
    {
        $project = Config::getField('project', 'name', 'zphp');
        $data = $project." runing!\n";
        $params = $this->_server->getParams();
        if(!empty($params)) {
            foreach($params as $key=>$val) {
                $data.= "key:{$key}=>{$val}\n";
            }
        }
        return $data;
    }
}

