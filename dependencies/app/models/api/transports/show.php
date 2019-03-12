<?php
namespace App\Models\Api\Transports;

class Show
{
    protected $request, $response, $container;
    public function __construct($container, $request, $response)
    {
        $this->request = $request;
        $this->response = $response;
        $this->container = $container;

    }
    public function run()
    {
        // показывает юзеров в системе
        try {

            // читаем из базы
            $db = $this->container['db'];
            $q = "select * from types;";
            $types = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from brands;";
            $brands = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from models;";
            $models = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from drives;";
            $drives = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from fuels;";
            $fuels = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from volums;";
            $volums = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from bodies;";
            $bodies = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from transmissions;";
            $transmissions = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
           
            return ["status" => "ok",
                "data" => [
                    "types" => $types,
                    "brands"=>$brands,
                    "models"=>$models,
                    "drives"=>$drives,
                    "fuels"=>$fuels,
                    "volums"=>$volums,
                    "bodies"=>$bodies,
                    "transmissions"=>$transmissions,
                ],
            ];
        } catch (RuntimeException | \Exception $e) {

            $exceptions["massege"] = $e->getMessage();
            return [
                "status" => "except",
                "data" => $exceptions,
            ];
        }
    }
}
