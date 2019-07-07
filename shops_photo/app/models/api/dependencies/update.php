<?php
namespace App\Models\Api\Dependencies;

class Update
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
        // обновляет зависимости

        try {
            $db = $this->container['db'];
            $apiReqwests = $this->container['api-requests'];

            // посылаем запрос к микросервису
            $rDep = $apiReqwests->RequestToDependencies;
            $services = $rDep->go("/api/services/show");
            if ($services == false) {
                throw new \Exception("Сервис зависимостей сервисов не отвечает.");
            }
            // сохраняем в конфиг
            file_put_contents(__DIR__ . '/../../../configs/services-config.json', json_encode($services));
            return ["status" => "ok",
                "data" => null,
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
