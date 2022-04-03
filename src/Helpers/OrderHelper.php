<?php

namespace kenarsuleyman\codeigniter4Efatura\Helpers;

use furkankadioglu\eFatura\Models\UnitType;
use kenarsuleyman\codeigniter4Efatura\Helpers\PriceHelper;

class OrderHelper
{
    private $itemTable;
    private $totalWithTax;
    private $totalWithoutTax;
    private $totalTax;

    public function __construct(array $order)
    {
        $this->itemTable = $this->itemTable($order['items']);

    }

    private function itemTable(array $items): array
    {
        $rows = [];
        $totalWithTax = 0;
        $totalTax = 0;
        foreach ($items as $item)
        {
            $price = new PriceHelper($item['price'], $product['taxRatio'] ?? 18 );
            $row = [
                'malHizmet' => $item['title'],
                'miktar' => $item['quantity'],
                'birim'  =>  UnitType::ADET,
                'birimFiyat' => $price->unitWithoutTax(),
                'kdvOrani' => $price->getTaxRatio(),
                'fiyat' => $price->totalWithoutTax(),
                'kdvTutari' => $price->totalTax(),
                'malHizmetTutari' => $price->totalPrice(),
                'vergiOrani' => 0,
                'iskontoOrani'  =>  0,
                'iskontoTutari'  =>  "0",
                'iskontoNedeni'  =>  "",
                'vergininKdvTutari'  =>  '0',
                'ozelMatrahTutari' => '0',
            ];
            $rows[] = $row;
            $totalWithTax += $price->totalPrice(false);
            $totalTax += $price->totalTax(false);
        }
        $this->totalWithTax = $totalWithTax;
        $this->totalTax = $totalTax;
        $this->totalWithoutTax = $totalWithTax - $totalTax;
        return $rows;
    }

    public function getItemTable()
    {
        return $this->itemTable;
    }

    public function totalWithTax(bool $format = true)
    {
        if($format)
            return number_format( $this->totalWithTax, 2 , ".", "");
        return $this->totalWithTax;
    }

    public function totalWithoutTax(bool $format = true)
    {
        if($format)
            return number_format( $this->totalWithoutTax, 2 , ".", "");
        return $this->totalWithoutTax;
    }

    public function totalTax(bool $format = true)
    {
        if($format)
            return number_format( $this->totalTax, 2 , ".", "");
        return $this->totalTax;
    }
}