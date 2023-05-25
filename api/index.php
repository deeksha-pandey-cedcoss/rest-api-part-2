<?php

use Phalcon\Loader;
use Phalcon\Mvc\Micro;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Event;
use Phalcon\Events\Manager;
use Phalcon\Acl\Adapter\Memory;
use Phalcon\Security\JWT\Builder;
use Phalcon\Security\JWT\Signer\Hmac;
use Phalcon\Security\JWT\Token\Parser;


// Use Loader() to autoload our model
$loader = new Loader();

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/html/');

require_once APP_PATH . '/vendor/autoload.php';

$loader->registerDirs(
    [
        APP_PATH . "/models/",
    ]
);

$loader->registerNamespaces(
    [
        'Store\Toys' => APP_PATH . '/models/',
    ]
);

$loader->register();



$manager = new Manager();
$manager->attach(
    'micro:beforeExecuteRoute',
    function (Event $event, $app) {

        $role = $_GET['role'];

        $signer  = new Hmac();
        $builder = new Builder($signer);
        $now        = new DateTimeImmutable();
        $issued     = $now->getTimestamp();
        $notBefore  = $now->modify('-1 minute')->getTimestamp();
        $expires    = $now->modify('+1 day')->getTimestamp();
        $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';

        $builder
            ->setAudience('https://target.phalcon.io')
            ->setContentType('application/json')
            ->setExpirationTime($expires)
            ->setId('abcd123456789')
            ->setIssuedAt($issued)
            ->setIssuer('https://phalcon.io')
            ->setNotBefore($notBefore)
            ->setSubject($role)
            ->setPassphrase($passphrase);

        $tokenObject = $builder->getToken();

        $tokenReceived = $tokenObject->getToken();
        $now           = new DateTimeImmutable();
        $id            = 'abcd123456789';

        $signer     = new Hmac();
        $passphrase = 'QcMpZ&b&mo3TPsPk668J6QH8JA$&U&m2';

        $parser      = new Parser();

        $tokenObject = $parser->parse($tokenReceived);

        $sub = $tokenObject->getClaims()->getPayload()['sub'];

        $new_r = $sub;

        $acl = new Memory();
        $acl->addRole('user');
        $acl->addRole('admin');
        $new = $_GET['_url'];

        $ar = explode("/", $new);
        $acl->addComponent(
            'products',
            []
        );
        $acl->allow("admin", '*', '*');
        $acl->deny("user", '*', '*');
        if (true === $acl->isAllowed($new_r, $ar[1], $ar[2])) {
        } else {
            echo 'Access denied :(';
            die;
        }
    }

);

$container = new FactoryDefault();

// Set up the database service


$container->set(
    'mongo',
    function () {
        $mongo = new MongoDB\Client(
            'mongodb+srv://deekshapandey:Deeksha123@cluster0.whrrrpj.mongodb.net/?retryWrites=true&w=majority'
        );

        return $mongo->api;
    },
    true
);

$app = new Micro($container);
$app->setEventsManager($manager);



// Retrieves all robots
$app->get(
    '/api/products',
    function () use ($app) {

        $options = [
            "limit" => (int)$_GET['limit'],
            "page" => (int)$_GET['page']
        ];
        $collection = $this->mongo->products->find(
            array(),
            $options,
            ['$skip' => (int)$_GET['limit'] * (int)$_GET['page']]
        );
        $data = [];
        foreach ($collection as $robot) {
            $data[] = [
                'id'   => $robot->_id,
                'pid'   => $robot->id,
                'name' => $robot->name,
                'price' => $robot->price,
            ];
        }

        echo json_encode($data);
    }
);
//  search by name
$app->get(
    '/api/products/search/{name}',
    function ($name) use ($app) {
        $product = $this->mongo->products->find();
        $data = array();
        $data = explode("%20", $name);
        foreach ($product as $products) {
            foreach ($data as $value) {
                $pattern = "/$value/i";
                if (preg_match_all($pattern, $products->name)) {
                    $result[] = [
                        'id'   =>  $products->_id,
                        'pid'   => $products->id,
                        'name' =>  $products->name,
                        'price' => $products->price,
                    ];
                }
            }
        }
        if (empty($result)) {
            echo "Not found";
        } else {
            echo json_encode($result);
        }
    }
);

$app->post(
    '/order/create',
    function () {
        $payload = [
            "name" => json_encode($_POST['name']),
            "address" => json_encode($_POST['address']),
            "product_id" => $_POST['product'],
            "quantity" => $_POST['quantity'],
            "status" => "placed",
            "order_id" => uniqid()
        ];
        $collection = $this->mongo->orders;
        $status = $collection->insertOne($payload);
        var_dump($status);
    }
);
$app->post(
    '/order/update/{id:[0-9]+}',
    function () {
        $robot = $app->request->getJsonRawBody();
        $collection = $this->mongo->orders;
        $updateResult = $collection->updateOne(
            ['id'  =>  $id],
            ['$set' => [
                "name" => $robot->name,
                "address" => $robot->address,
                "quantity" => $robot->quantity,
                "status" => "declined",

            ]]
        );
        print_r($updateResult);
        die;
    }
);
$app->get(
    '/api/order',
    function () use ($app) {
        $collection = $this->mongo->orders->find();
        $data = [];
        foreach ($collection as $robot) {
            $data[] = [
                'id'   => $robot->_id,
                'pid'   => $robot->id,
                'name' => $robot->name,
                'price' => $robot->price,
                "address" => $robot->address,
                "quantity" => $robot->quantity,
                "status" => $robot->status,
            ];
        }

        echo json_encode($data);
    }
);
$app->handle(
    $_SERVER["REQUEST_URI"]
);
