<?php


use Phalcon\Mvc\Controller;

session_start();
class LoginController extends Controller
{
    public function indexAction()
    {
        // default action
    }
    public function loginAction()
    {

        if ($this->request->isPost()) {
            $email = $this->request->getPost('email');
            $pass = $this->request->getPost("password");


            $collection = $this->mongo->Users;
            $admin = $collection['admin'];
            $data = $collection->findOne(["email" => $email, "password" => $pass]);


            if ($admin) {
                if ($data->admin == '1') {
                    $_SESSION['role'] = 'admin';
                    $this->response->redirect('/orders/display');
                }
            } else {
                if ($data->email == $_POST['email'] && $data->email != '') {
                    $_SESSION['role'] = 'user';
                    $this->response->redirect('/place/new');
                } else {
                    $this->response->redirect('login');
                }
            }
        }
    }
}
