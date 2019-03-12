<?php
namespace App\Models\Api\Locations;

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

            // пишем в базу
            $db = $this->container['db'];
            $q = "select * from countries;";
            $countries = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();
            $q = "select * from cities;";
            $cities = $db->query($q, \PDO::FETCH_ASSOC)->fetchAll();

            return ["status" => "ok",
                "data" => [
                    "countries" => $countries,
                    "cities" => $cities,
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
