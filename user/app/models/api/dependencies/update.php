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
            // пишем в базу
            $db = $this->container['db'];
            // $q = "TRUNCATE countries CASCADE;";
            // $sth = $db->query($q, \PDO::FETCH_ASSOC);
            // $sth->fetch();
            // посылаем запрос к микросервису токенов
            // для создания токена для юзера
            $apiReqwests = $this->container['api-requests'];
            $rShowLocations = $apiReqwests->RequestShowLocations;
            $locations = $rShowLocations->go();
            // если не удалось создать токен
            if ($locations == false) {
                throw new \Exception("Сервис зависимостей не отвечает.");
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
            // $db->commit();

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
