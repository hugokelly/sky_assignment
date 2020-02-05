<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__.'/vendor/autoload.php';

//Instantiate App
$app = AppFactory::create();
$app->setBasePath('/api');

//Add error middleware
$app->addErrorMiddleware(true, true, true);

//Database Connection
$config = include(__DIR__.'/config.php');
$db = new PDO('mysql:host='.$config['host'].';dbname='.$config['dbname'], $config['user'], $config['password']);

//Default Route (just return status true for now - unused)
$app->any('/', function (Request $request, Response $response){
    $response->getBody()->write((string)json_encode(['api_status' => true]));
    $response = $response->withHeader('Content-Type', 'application/json');
    return $response;
});

//CPU Data Gatherer
$app->post('/get_cpu_data', function (Request $request, Response $response) use ($db){
    //grab the posted timestamps
    $args = $request->getParsedBody();
    $timestamp_from = (int)$args['timestamp_from'];
    $timestamp_to = (int)$args['timestamp_to'];

    //start the query
	try{		
		$command = $db->prepare('SELECT timestamp, cpuLoad, concurrency FROM cpu_log WHERE timestamp >= :timestamp_from AND timestamp <= :timestamp_to;');
		$command->bindValue(':timestamp_from', $timestamp_from, PDO::PARAM_INT);
		$command->bindValue(':timestamp_to', $timestamp_to, PDO::PARAM_INT);
		$command->execute();
        //format the result array for easy use in the front end
        $result = [];
        while($row = $command->fetchObject()){
            $result[] = [(int)$row->timestamp, (float)$row->cpuLoad, (int)$row->concurrency];
        }
        $response->getBody()->write((string)json_encode(['status' => !empty($result), 'results' => $result]));
    //if there was an exception
    }catch(Exception $e){
        $response->getBody()->write((string)json_encode(['status' => false, 'results' => $e->getMessage()]));
    }

    //output response
    $response = $response->withHeader('Content-Type', 'application/json');
    return $response;
});

//Run the App
$app->run();