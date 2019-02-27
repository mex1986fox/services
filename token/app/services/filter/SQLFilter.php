<?php
namespace App\Services\Filter;

use \Zend\filter\AbstractFilter;

class SQLFilter extends AbstractFilter
{
    public function filter($str)
    {
        //зашифровать кавычки
        //убить теги

        // $str = filter_var($str, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        //$str=filter_var($str,FILTER_SANITIZE_SPECIAL_CHARS);

        //убить SQL выражения
        $specSQL = ["'", "select", "insert", "update", "merge",
            "delete", "drop", "create", "alter",
            "crant", "revoke", "start", "explain",
            "open", "close", "prepare"];
        $ireplSQL = ['"', "&#127select&#127", "&#127insert&#127", "&#127update&#127", "&#127merge&#127",
            "&#127delete&#127", "&#127drop&#127", "&#127create&#127", "&#127alter&#127",
            "&#127crant&#127", "&#127revoke&#127", "&#127start&#127", "&#127explain&#127",
            "&#127open&#127", "&#127close&#127", "&#127prepare&#127"];

        $str = str_ireplace($specSQL, $ireplSQL, $str);
        $str = pg_escape_string($str);

        return $str;
    }
}
