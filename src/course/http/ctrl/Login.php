<?php
/**
 * Created by PhpStorm.
 * User: lancelot
 * Date: 16-7-9
 * Time: 下午6:38
 */
namespace ctrl;

class Login
{
    public function login($request)
    {
    	$post = isset($request->post) ? $request->post : array();

    	// TODO 
        return "login success";
    }
}