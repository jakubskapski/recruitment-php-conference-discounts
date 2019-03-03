<?php

namespace RstGroup\Recruitment\ConferenceSystem\Discount;

class DiscountService
{
    private $discountsToProcess = [];
    private $totalDiscount = 0;
    private $price;

    public function __construct($price)
    {
        $this->price = $price;
    }

    public function addDiscount(DiscountInterface $discount)
    {
        if(!$discount instanceof ExcludesCodeDiscountInterface)
            array_push($this->discountsToProcess, $discount);
        else
            array_unshift($this->discountsToProcess, $discount);

        return $this;
    }

    public function calculate()
    {
        $this->calculateFromSetDiscounts();

        return $this->totalDiscount;
    }

    protected function calculateFromSetDiscounts()
    {
        $excludeCodeDiscount = false;
        foreach ($this->discountsToProcess as $discount) {
            if($discount instanceof CodeDiscount && $excludeCodeDiscount === true)
                continue;

            $value = $discount->calculate();
            $this->sumUpDiscount($value);

            if($discount instanceof ExcludesCodeDiscountInterface && $discount->getExcludeCodeDiscount()) {
                $excludeCodeDiscount = true;
            }
        }
    }

    private function sumUpDiscount($discountObject)
    {
        if ($discountObject instanceof Percentage)
            $this->totalDiscount += $this->calculateValueFromPercent($discountObject);
        elseif ($discountObject instanceof Value)
            $this->totalDiscount += $discountObject->getValue();
    }

    private function calculateValueFromPercent($percentObject)
    {
        return $this->price * ($percentObject->getValue() / 100);
    }
}