<?php


class Animal
{
    function hasHead()
    {
        return true;
    }
}

class FurryAnimal extends Animal
{
    function hasFur()
    {
        return true;
    }
}

class Rabbit extends FurryAnimal
{
    function isCute()
    {
        return true;
    }
}


$r = new Rabbit();
$r->hasHead();
$r->hasFur();
$r->isCute();
