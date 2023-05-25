<?php

use Phalcon\Mvc\Controller;

session_start();
class  PlaceController extends Controller
{
    public function indexAction()
    {
        // defalut action
    }
    public function newAction()
    {
        // fetch all the data from products/get
        $ch = curl_init();
        $url = "http://172.24.0.5/api/products?role=admin";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        curl_close($ch);

        $this->view->data = json_decode($output);
    }
    public function placeAction()
    {
        if ($_POST['name'] == '' || $_POST['quantity'] < 1) {
            echo "<h3>The fields are not correct</h3>";
            die;
        }
        $ch = curl_init();
        $url = "http://172.24.0.5/order/create?role=$_SESSION[role]";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $_POST);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $r = curl_exec($ch);
        curl_close($ch);
        $this->response->redirect('/place/new');
    }
    public function displayAction()
    {
        $ch = curl_init();
        $url = "http://172.24.0.5/api/order?role=$_SESSION[role]";
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $output = curl_exec($ch);
        curl_close($ch);
        $this->view->data = (json_decode($output, true));
    }
}
