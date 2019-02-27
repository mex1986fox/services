<?php
namespace App\Services\Filter;

use \Zend\filter\AbstractFilter;

class ChainFilter extends AbstractFilter
{
    protected $filters;

    public function filter($value)
    {
        // $arrValue=[
        //     $value, $value, ...
        // ]
        // $arrFiltesr=[
        //     ['filter'=>$objFilter],
        //     ['filter'=>$objFilter, 'otions'=>['cahain'=>"\r\n\t"]],
        //     ['filter'=>$objFilter],
        //     ...
        // ]
        $newValue = $value;
        foreach ($this->filters as $filter) {
            if (isset($filter['otions'])) {
                $newValue = $filter['filter']->filter($newValue, $filter['otions']);

            } else {
                $newValue = $filter['filter']->filter($newValue);
            }

        }
        return $newValue;
    }
    public function setFilters($arrFilters)
    {
        $this->filters = $arrFilters;
    }
}
