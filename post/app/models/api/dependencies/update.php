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

            // посылаем запрос к микросервису
            $locations = $rDep->go("/api/locations/show");
            if ($locations == false) {
                throw new \Exception("Сервис зависимостей локаций не отвечает.");
            }
            $q =
                ' insert into countries (country_id, name) values (:country_id, :name) ' .
                ' on conflict (country_id) do ' .
                ' update set country_id=:country_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($locations->countries as $v) {
                $stm->bindValue(':country_id', $v->country_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into subjects (subject_id, country_id, name) values (:subject_id, :country_id, :name) ' .
                ' on conflict (subject_id) do ' .
                ' update set subject_id=:subject_id, country_id=:country_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($locations->subjects as $v) {
                $stm->bindValue(':subject_id', $v->subject_id);
                $stm->bindValue(':country_id', $v->country_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into cities (city_id, subject_id, country_id, name) values (:city_id, :subject_id, :country_id, :name) ' .
                ' on conflict (city_id) do ' .
                ' update set city_id=:city_id, subject_id=:subject_id, country_id=:country_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($locations->cities as $v) {
                $stm->bindValue(':city_id', $v->city_id);
                $stm->bindValue(':subject_id', $v->subject_id);
                $stm->bindValue(':country_id', $v->country_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }

            // посылаем запрос к микросервису
            $transport = $rDep->go("/api/transports/show");
            if ($transport == false) {
                throw new \Exception("Сервис зависимостей транспорта не отвечает.");
            }
            $q =
                ' insert into types (type_id, name) values (:type_id, :name) ' .
                ' on conflict (type_id) do ' .
                ' update set type_id=:type_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($transport->types as $v) {
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into brands (brand_id, type_id, name) values (:brand_id, :type_id, :name) ' .
                ' on conflict (brand_id) do ' .
                ' update set brand_id=:brand_id, type_id=:type_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($transport->brands as $v) {
                $stm->bindValue(':brand_id', $v->brand_id);
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into models (model_id, brand_id, type_id, name) values (:model_id, :brand_id, :type_id, :name) ' .
                ' on conflict (model_id) do ' .
                ' update set model_id=:model_id, brand_id=:brand_id, type_id=:type_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($transport->models as $v) {
                $stm->bindValue(':model_id', $v->model_id);
                $stm->bindValue(':brand_id', $v->brand_id);
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into drives (drive_id, type_id, name) values (:drive_id, :type_id, :name) ' .
                ' on conflict (drive_id) do ' .
                ' update set drive_id=:drive_id, type_id=:type_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($transport->drives as $v) {
                $stm->bindValue(':drive_id', $v->drive_id);
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into fuels (fuel_id, type_id, name) values (:fuel_id, :type_id, :name) ' .
                ' on conflict (fuel_id) do ' .
                ' update set fuel_id=:fuel_id, type_id=:type_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($transport->fuels as $v) {
                $stm->bindValue(':fuel_id', $v->fuel_id);
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into volums (volume_id, type_id, value) values (:volume_id, :type_id, :value) ' .
                ' on conflict (volume_id) do ' .
                ' update set volume_id=:volume_id, type_id=:type_id, value=:value ';
            $stm = $db->prepare($q);
            foreach ($transport->volums as $v) {
                $stm->bindValue(':volume_id', $v->volume_id);
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':value', $v->value);
                $stm->execute();
            }
            $q =
                ' insert into transmissions (transmission_id, type_id, name) values (:transmission_id, :type_id, :name) ' .
                ' on conflict (transmission_id) do ' .
                ' update set transmission_id=:transmission_id, type_id=:type_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($transport->transmissions as $v) {
                $stm->bindValue(':transmission_id', $v->transmission_id);
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }
            $q =
                ' insert into bodies (body_id, type_id, name) values (:body_id, :type_id, :name) ' .
                ' on conflict (body_id) do ' .
                ' update set body_id=:body_id, type_id=:type_id, name=:name ';
            $stm = $db->prepare($q);
            foreach ($transport->bodies as $v) {
                $stm->bindValue(':body_id', $v->body_id);
                $stm->bindValue(':type_id', $v->type_id);
                $stm->bindValue(':name', $v->name);
                $stm->execute();
            }

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
