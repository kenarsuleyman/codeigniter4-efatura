<?php

namespace kenarsuleyman\codeigniter4Efatura\Helpers;

use kenarsuleyman\codeigniter4Efatura\Helpers\PriceHelper;
use furkankadioglu\eFatura\Models\UnitType;


class EArsivOrderHelper
{

    private $itemTable;
    private $totalWithTax;
    private $totalWithoutTax;
    private $totalTax_18;
    private $totalTax_8;
    private $totalTax_1;



    public function __construct(array $items)
    {
        $this->parseOrder($items);
    }

    private function parseOrder($items)
    {
        $totalTax_18 = 0;
        $totalTax_8 = 0;
        $totalTax_1 = 0;
        $totalWithTax = 0;
        $totalTax = 0;

        foreach ($items as $item)
        {
            $price = new PriceHelper($item, true);
            if($price->getTaxRate() == 18)
            {
                $totalTax_18 += $price->totalTax(false);

            }elseif ($price->getTaxRate() == 8){

                $totalTax_8 += $price->totalTax(false);

            }elseif ($price->getTaxRate() == 1){

                $totalTax_1 += $price->totalTax(false);

            }
            $totalWithTax += $price->totalPrice(false);
            $totalTax += $price->totalTax(false);
        }
        $this->totalTax_18 = $totalTax_18;
        $this->totalTax_8 = $totalTax_8;
        $this->totalTax_1 = $totalTax_1;

        $this->totalWithTax = $totalWithTax;
        $this->totalTax = $totalTax;
        $this->totalWithoutTax = $totalWithTax - $totalTax;
    }

    public function getTotalTax18(bool $format = true)
    {
        if($format)
            return number_format( $this->totalTax_18, 2 , ",", ".");
        return $this->totalTax_18;
    }

    public function getTotalTax8(bool $format = true)
    {
        if($format)
            return number_format( $this->totalTax_8, 2 , ",", ".");
        return $this->totalTax_8;
    }

    public function getTotalTax1(bool $format = true)
    {
        if($format)
            return number_format( $this->totalTax_1, 2 , ",", ".");
        return $this->totalTax_1;
    }

    public function totalWithTax(bool $format = true)
    {
        if($format)
            return number_format( $this->totalWithTax, 2 , ",", ".");
        return $this->totalWithTax;
    }

    public function totalWithoutTax(bool $format = true)
    {
        if($format)
            return number_format( $this->totalWithoutTax, 2 , ",", ".");
        return $this->totalWithoutTax;
    }
}