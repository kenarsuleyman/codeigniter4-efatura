<?php

namespace kenarsuleyman\codeigniter4Efatura\Helpers;

class PriceHelper
{
    private $unitPrice;
    private $quantity;
    private $taxRate;
    private $unitWithoutTax;
    private $unitTax;
    private $totalTax;
    private $totalWithoutTax;
    private $totalPrice;

    public function __construct(array $item, bool $fromAPI = false)
    {
        if($fromAPI)
        {
            $this->unitPrice = $item['malHizmetTutari'] / $item['miktar'];
            $this->taxRate = $item['kdvOrani'];
            $this->quantity = $item['miktar'];
        }else{
            $this->unitPrice = $item['price'];
            $this->taxRate = $item['taxRate'] ?? 18;
            $this->quantity = $item['quantity'];
        }

        $this->unitWithoutTax = $this->unitPrice / ( 1 + $this->taxRate/100 );
        $this->unitTax = $this->unitPrice - $this->unitWithoutTax;

        $this->totalPrice = $this->unitPrice * $this->quantity;
        $this->totalWithoutTax = $this->unitWithoutTax * $this->quantity;
        $this->totalTax = $this->unitTax * $this->quantity;
    }

    public function unitPrice($format = true)
    {
        if($format)
            return number_format( $this->unitPrice, 2 , ".", "");
        return $this->unitPrice;
    }

    public function unitWithoutTax($format = true)
    {
        if($format)
            return number_format( $this->unitWithoutTax, 2 , ".", "");
        return $this->unitWithoutTax;
    }

    public function unitTax($format = true)
    {
        if($format)
            return number_format( $this->unitWithoutTax, 2 , ".", "");
        return $this->unitWithoutTax;
    }

    public function totalTax($format = true)
    {
        if($format)
            return number_format( $this->totalTax, 2 , ".", "");
        return $this->totalTax;
    }

    public function totalWithoutTax($format = true)
    {
        if($format)
            return number_format( $this->totalWithoutTax, 2 , ".", "");
        return $this->totalWithoutTax;
    }

    public function totalPrice($format = true)
    {
        if($format)
            return number_format( $this->totalPrice, 2 , ".", "");
        return $this->totalPrice;
    }

    public function getTaxRate(): int
    {
        return $this->taxRate;
    }

    public function getQuantity(): float
    {
        return $this->quantity;
    }
}