<?php

use Phalcon\Mvc\Controller;

class ProductController extends Controller
{
    public function indexAction()
    {
        $ch = curl_init();
        $url = "http://172.24.0.5/api/products/?role=admin";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $contents = curl_exec($ch);
        echo "<pre>";
        print_r($contents);
        die;

     
    }
}
