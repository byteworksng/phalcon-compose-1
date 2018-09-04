<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Loader;
//use Phalcon\Db\Adapter\Pdo\Mysql as PdoMysql;
use Phalcon\Db\Adapter\Pdo\Postgresql as PdoPostgres;

error_reporting(E_ALL);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();

    /**
     * Include Services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

$loader->registerNamespaces(
    [
        'modeldir' => __DIR__ . '/models/',
    ]
);

$loader->register();

// Set up the database service
$di->set(
    'db',
    function () {
        return new PdoPostgres(
            [
                'host'     => 'postgres',
                'username' => 'postgres',
                'password' => 'postgres',
                'dbname'   => 'kindle',
            ]
        );
    }
);

// Create and bind the DI to the application
$app = new Micro($di);

// Retrieves all robots
$app->get(
    '/api/books',
    function () use ($app) {
        $phql = 'SELECT * FROM titles ORDER BY title';

        $books= $app->modelsManager->executeQuery($phql);

        $data = [];

        foreach ($books as $book) {
            $data[] = [
		'id' => $book->id,
                'amazon_id'   => $book->amazon_id,
                'title' => $book->title,
                'publication_date' => $book->publication_date,
                'evaluation' => $book->evaluation,
                'review_cnt' => $book->review_cnt,
                'now_price' => $book->now_price,
                'lowest_price' => $book->lowest_price,
            ];
        }

        echo json_encode($data);
    }
);

// Searches for books with $name in their name
$app->get(
    '/api/books/search/{title}',
    function ($title) use ($app) {
        $phql = 'SELECT * FROM titles WHERE title LIKE :title: ORDER BY title';

        $books = $app->modelsManager->executeQuery(
            $phql,
            [
                'title' => '%' . $title. '%'
            ]
        );

        $data = [];

        foreach ($books as $book) {

            $data[] = [
		'id' => $book->id,
                'amazon_id'   => $book->amazon_id,
                'title' => $book->title,
                'publication_date' => $book->publication_date,
                'evaluation' => $book->evaluation,
                'review_cnt' => $book->review_cnt,
                'now_price' => $book->now_price,
                'lowest_price' => $book->lowest_price,
            ];
        }

        echo json_encode($data);
    }
);

// Retrieves books based on primary key
$app->get(
    '/api/books/id/{id}',
    function ($id) {
        $phql = 'SELECT * FROM title WHERE id = :id:';

        $book = $app->modelsManager->executeQuery(
            $phql,
            [
                'id' => $id,
            ]
        )->getFirst();

        // Create a response
        $response = new Response();

        if ($book === false) {
            $response->setJsonContent(
                [
                    'status' => 'NOT-FOUND'
                ]
            );
        } else {
            $response->setJsonContent(
                [
                    'status' => 'FOUND',
                    'data'   => [
                'id'   => $book->id,
                'amazon_id'   => $book->amazon_id,
                'title' => $book->title,
                'publication_date' => $book->publication_date,
                'evaluation' => $book->evaluation,
                'review_cnt' => $book->review_cnt,
                'now_price' => $book->now_price,
                'lowest_price' => $book->lowest_price,
                    ]
                ]
            );
        }

        return $response;
    }
);

$app->handle();

} catch (\Exception $e) {
      echo $e->getMessage() . '<br>';
      echo '<pre>' . $e->getTraceAsString() . '</pre>';
}
