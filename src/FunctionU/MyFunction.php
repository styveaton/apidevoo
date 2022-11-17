<?php

namespace App\FunctionU;

class MyFunction
{
    
    public function removeSpace(string $value)
    {
        return str_replace(' ', '', rtrim(trim($value)));
    }
}
