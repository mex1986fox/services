<?php
namespace App\Services\Filter;

class FilterFactory
{

    protected $filters = [];

    public function addFilter($nameFilter)
    {
        if (!array_key_exists($nameFilter, $this->filters)) {
            $nspace = "\\Zend\\Filter\\$nameFilter";
            if (!class_exists($nspace)) {
                $nspace = "\\App\\Services\\Filter\\$nameFilter";
            }
            $this->filters[$nameFilter] = new $nspace();
        }
    }

    public function __get($nameFilter)
    {
        $this->addFilter($nameFilter);
        return $this->filters[$nameFilter];
    }
}
