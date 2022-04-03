<?php

namespace kenarsuleyman\codeigniter4Efatura\Helpers;

use DateTime;
use Dompdf\Dompdf;
use Dompdf\Options;
use kenarsuleyman\codeigniter4Efatura\Helpers\EArsivOrderHelper;

class HtmlHelper
{

    private $template;
    private $html;
    private $formatted_data;

    public function __construct()
    {
        $this->template = file_get_contents(__DIR__ . '/../Templates/Invoice.html');
    }

    private function itemTable(array $data)
    {
        $currency = $this->formatCurrency($data["paraBirimi"]);
        $html = "";
        $i = 1;
        foreach ($data['malHizmetTable'] as $item) {
            $html = "<tr>";
            $html .= "<td>" . $i . "</td>";
            $html .= "<td>" . $item["malHizmet"] . "</td>";
            $html .= "<td>" . number_format($item["miktar"], 2, ",", ".") . " " . $this->formatUnit($item["birim"]) . "</td>";
            $html .= "<td>" . number_format($item["birimFiyat"], 2, ",", ".") . " " . $currency . "</td>";
            $html .= "<td>" . "%" . number_format($item["iskontoOrani"], 2, ",", ".") . "</td>";
            $html .= "<td>" . number_format($item["iskontoTutari"], 2, ",", ".") . " " . $currency . "</td>";
            $html .= "<td>" . "%" . number_format($item["kdvOrani"], 2, ",", ".") . "</td>";
            $html .= "<td>" . number_format($item["kdvTutari"], 2, ",", ".") . " " . $currency . "</td>";
            $html .= "<td>" . number_format($item["fiyat"], 2, ",", ".") . " " . $currency . "</td>";
            $html .= "</tr>";
            $i++;
        }


        return $html;
    }

    private function totalTable(array $data)
    {
        $currency = $this->formatCurrency($data["paraBirimi"]);
        $EArsivOrderHelper = new EArsivOrderHelper($data['malHizmetTable']);
        $temp = [
            'kdv_18' => $EArsivOrderHelper->getTotalTax18(false),
            'kdv_8' => $EArsivOrderHelper->getTotalTax8(false),
            'kdv_1' => $EArsivOrderHelper->getTotalTax1(false),
            'toplam_tutar' => $EArsivOrderHelper->totalWithoutTax(false),
            'odenecek_tutar' => $EArsivOrderHelper->totalWithTax(false),
        ];
        $html = sprintf('<tr><td style="width: 200px"><strong>Mal Hizmet Toplam Tutarı</strong></td><td style="width: 90px">%s</td></tr>'
            , number_format($temp["toplam_tutar"], 2, ",", ".") . " " . $currency);
        $html .= sprintf('<tr><td style="width: 200px"><strong>Toplam İskonto</strong></td><td style="width: 90px">%s</td></tr>'
            , '0.00 TL');
        if($temp['kdv_18'])
        {
            $html .= sprintf('<tr><td style="width: 200px"><strong>Hesaplanan KDV(%%18)</strong></td><td style="width: 90px">%s</td></tr>'
                , number_format($temp["kdv_18"], 2, ",", ".") . " " . $currency);
        }
        if($temp['kdv_8'])
        {
            $html .= sprintf('<tr><td style="width: 200px"><strong>Hesaplanan KDV(%%8)</strong></td><td style="width: 90px">%s</td></tr>'
                , number_format($temp["kdv_8"], 2, ",", ".") . " " . $currency);
        }
        if($temp['kdv_1'])
        {
            $html .= sprintf('<tr><td style="width: 200px"><strong>Hesaplanan KDV(%%1)</strong></td><td style="width: 90px">%s</td></tr>'
                , number_format($temp["kdv_1"], 2, ",", ".") . " " . $currency);
        }
        $html .= sprintf('<tr><td style="width: 200px"><strong>Vergiler Dahil Toplam Tutar</strong></td><td style="width: 90px">%s</td></tr>'
            , number_format($temp["odenecek_tutar"], 2, ",", ".") . " " . $currency);
        $html .= sprintf('<tr><td style="width: 200px"><strong>Ödenecek Tutar</strong></td><td style="width: 90px">%s</td></tr>'
            , number_format($temp["odenecek_tutar"], 2, ",", ".") . " " . $currency);
        return $html;
    }

    private function formatUnit(string $birim){
        switch ($birim){
            case "C62":
                return "Adet";
            default:
                return "";
        }
    }

    private function formatCurrency(string $currency){
        switch ($currency){
            case "TRY":
                return "TL";
            default:
                return "";
        }
    }

    public function formatData(array $data)
    {

        $date = DateTime::createFromFormat('d/m/Y H:i:s', $data['faturaTarihi'] . ' ' . $data["saat"]);
        $formatted = [
            'faturaUuid' => $data['faturaUuid'],
            'belgeNumarasi' => $data['belgeNumarasi'],
            'vknTckn' => $data['vknTckn'],
            'tel' => $data['tel'],
            'eposta' => $data['eposta'],
            'tarih' => $date->format('d-m-Y H:i'),
            'alici' => $data['aliciUnvan'] ??  $data['aliciAdi'] . ' ' . $data['aliciSoyadi'],
            'adres' => $data["bulvarcaddesokak"],
            'hizmet_table' => $this->itemTable($data),
            'total_table' => $this->totalTable($data),
        ];
        $this->formatted_data = $formatted;
    }

    public function export(string $path)
    {
        foreach ($this->formatted_data as $key => $value){
            $this->template = str_replace("{" . $key . "}", $value, $this->template);
        }
        $dom_opt = new Options();
        $dom_opt->setDpi(109);
        $dompdf = new Dompdf($dom_opt);
        $dompdf->loadHtml($this->template);
        $dompdf->setPaper('A4' );
        $dompdf->render();
        $output = $dompdf->output();
        file_put_contents($path, $output);
    }

}