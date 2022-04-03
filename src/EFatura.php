<?php

namespace kenarsuleyman\codeigniter4Efatura;

use DateTime;
use furkankadioglu\eFatura\Models\Invoice;
use furkankadioglu\eFatura\InvoiceManager;
use kenarsuleyman\codeigniter4Efatura\Helpers\HtmlHelper;
use kenarsuleyman\codeigniter4Efatura\Helpers\OrderHelper;


class EFatura
{
    private $conn;
    private $logo;
    private $signature;

    /**
     * @param string $username e-Arşiv Kullanıcı Adı
     * @param string $password e-Arşiv Şifre
     * @param string $logo Logo Yolu
     * @param string $signature Kaşe/İmza Resim Yolu
     */
    public function __construct(string $username, string $password, string $logo = "", string $signature = "")
    {
        $this->conn = new InvoiceManager();
        $this->conn->setCredentials($username, $password);
        $this->conn->connect();
    }

    /**
     * @param array $customer
     * @param array $order
     * @param bool $kurumsal
     * @return string e-Arşiv Fatura UUID
     */
    public function create(array $customer, array $order, bool $kurumsal = false) : string
    {
        $datetime = DateTime::createFromFormat( 'Y-m-d H:i:s', $order['date'] );
        $orderHelper = new OrderHelper($order);

        $faturaDetay = [
            'faturaTarihi' => $datetime->format("d/m/Y"),
            'saat' => $datetime->format("H:i:s"),
            'paraBirimi'  =>  "TRY",
            'dovzTLkur'  =>  "0",
            'faturaTipi'  =>  "SATIS",
            'hangiTip'  =>  "5000/30000",
            'ulke'  =>  "Türkiye",
            'sehir'  =>  " ",
            'tip'  =>  "İskonto",
            'eposta' => $customer['email'],
            'tel' => $customer['telefon'],
            'bulvarcaddesokak' => $customer["adres"],
            'malhizmetToplamTutari' => $orderHelper->totalWithoutTax(),
            'matrah' => $orderHelper->totalWithoutTax(),
            'toplamIskonto' => "0",
            'hesaplanankdv' => $orderHelper->totalTax(),
            'vergilerToplami' => $orderHelper->totalTax(),
            'vergilerDahilToplamTutar' => $orderHelper->totalWithTax(),
            'odenecekTutar' => $orderHelper->totalWithTax(),
            'malHizmetTable'  => $orderHelper->getItemTable()
        ];

        if ( $kurumsal ){
            $faturaDetay['aliciUnvan'] = $customer['unvan'];
            $faturaDetay['vergiDairesi'] = $customer['vergi_daire'];
            $faturaDetay['vknTckn'] = $customer['vergi_no'];
        }else{
            $faturaDetay['aliciAdi'] = $customer['ad'];
            $faturaDetay['aliciSoyadi'] = $customer['soyad'];
            $faturaDetay['vknTckn'] = $customer['tc'] ?? '11111111111';
        }

        $invoice = new Invoice();
        $invoice->mapWithTurkishKeys($faturaDetay);
        $this->conn->setInvoice($invoice);
        $this->conn->createDraftBasicInvoice();
        return $invoice->getUuid();
    }

    public function savePdf(string $uuid, string $path)
    {
        $invoice = new Invoice();
        $invoice->setUuid($uuid);
        $faturaDetay = $this->conn->setInvoice($invoice)
            ->getInvoiceFromAPI();
    }
}