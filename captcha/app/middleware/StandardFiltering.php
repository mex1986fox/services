<?php
namespace App\Middleware;

/*
 * Применяет фильтрацию ко всем параметрам запроса
 * - уничтожает пробелы до и после
 * - убирает теги
 * - коментирует спецсимволами sql инъекции
 */

class StandardFiltering
{
    protected $container;
    public function __construct($container)
    {
        $this->container = $container;
    }
    public function __invoke($request, $response, $next)
    {
        if ($request->isPost()) {
            $p = $request->getParsedBody();
        }
        if ($request->isGet()) {
            $p = $request->getQueryParams();
        }
        // var_dump($p);
        if (isset($p)) {
            // фильтруем параметры
            $filters = $this->container['filters'];
            // обрезает пробелы
            $fTrim = $filters->StringTrim;
            // убирает HTML сущности
            $fTag = $filters->StripTags;
            // редактирует sql выражения
            $fSQL = $filters->SQLFilter;
            // организуем из фильтров цепь
            $fChain = $filters->ChainFilter;
            $fChain->setFilters([
                ['filter' => $fTrim,
                    'options' => ['charlist' => '\r\n\t'],
                ],
                ['filter' => $fTag],
                ['filter' => $fSQL],

            ]);
            // применяем цепь фильтров ко всем параметрам
            foreach ($p as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $key2 => $value2) {
                        $p[$key][$key2] = $fChain->filter($value2);
                    }
                } else {
                    $p[$key] = $fChain->filter($value);
                }

            }
            var_dump($p);
            // устанавливаем новые параметры в объект запроса
            // и обновляем его
            $request = $request->withQueryParams($p);
        }

        return $next($request, $response);

    }
}
