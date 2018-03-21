<?php

namespace B3none\emtapi\Factories;

class StationConstantFactory
{
    /**
     * This gets creates and validates a constant name based on the name of a station.
     * If the first character of the string is a number we append it with an underscore.
     * @param array $station
     * @return string
     */
    public function create(array $station) : string
    {
        $label = $station['label'];

        $label = strtoupper($label);
        $label = str_replace([" ", ",", "-"], "_", $label);
        $label = str_replace(["(", ")", "'"], "", $label);
        $label = str_replace("&", "AND", $label);

        if (is_numeric($label[0])) {
            $label = "_" . $label;
        }

        return $this->validateConstant($label);
    }

    /**
     * This makes sure that we only have alphanumeric and underscores
     * in the constant name.
     *
     * @param string $string
     * @return string
     */
    protected function validateConstant(string $string) : string
    {
        $newStr = '';
        foreach(str_split($string) as $chr) {
            if (ctype_alnum($chr) || $chr === '_') {
                $newStr .= $chr;
            }
        }

        return $newStr;
    }
}