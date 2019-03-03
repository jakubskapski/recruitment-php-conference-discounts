<?php

namespace RstGroup\Recruitment\ConferenceSystem\Discount;


class Value implements DiscountValueInterface
{
    private $value;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function getValue()
    {
        return floatval($this->value);
    }
}