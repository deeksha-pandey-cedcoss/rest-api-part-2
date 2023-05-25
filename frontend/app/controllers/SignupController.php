<?php

use Phalcon\Mvc\Controller;

class SignupController extends Controller
{
    public function indexAction()
    {
        //    defalut action

    }
    public function registerAction()
    {
        $collection = $this->mongo->Users;


        $name = $this->request->getPost("name");
        $email = $this->request->getPost("email");
        $password = $this->request->getPost("password");

        $arr = [
            "name" => $name,
            "email" => $email,
            "password" => $password,
            'admin' => (bool) ($_POST['adminSignup'] == 'admin')
        ];
        $status = $collection->insertOne($arr);


        $this->response->redirect("login");
    }
}
