<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'libraries\TCPDF-main\tcpdf.php');

class Invoice extends CI_Controller {

    public function generate($id)
    {
        // Example data (in a real app, you can retrieve this data from DB or passed parameters)
        $transaction_amount = 555.75;
        $transaction_type = 'L'; // 'L' for local, 'I' for international
        $commission_rate = ($transaction_type == 'L') ? 1.64 : 3.00;
        
        // Client data (can also be fetched from DB or passed)
        $client_name = 'John Doe';
        $client_email = 'johndoe@example.com';
        $client_address = '123 Example St, City, Country';
        
        $commission_ht = ($transaction_amount * $commission_rate) / 100;
        $tva = $commission_ht * 0.10;
        $total_ttc = $commission_ht + $tva;
        $solde_net = $transaction_amount - $total_ttc;

        // Generate PDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        // HTML content with styling
        $html = '
        <style>
            body { font-family: Helvetica; font-size: 10pt; }
            .header { margin-bottom: 10px; }
            .company-name { font-size: 14pt; font-weight: bold; }
            .invoice-info { margin: 3px 0; }
            table { width: 100%; border-collapse: collapse; margin: 10px 0; }
            th, td { border: 1px solid #333; padding: 5px; }
            th { background-color: #f2f2f2; }
            .total-section { margin-top: 15px; }
            .footer { font-size: 8pt; margin-top: 20px; }
            .text-right { text-align: right; }
            .bordered { border: 1px solid #333; padding: 4px; }
        </style>

        <div class="header">
            <div class="company-name">webmania</div>
            <div style="font-size: 12pt; margin: 5px 0;">'.$client_name.'</div>
            
            <table style="width: 100%">
                <tr>
                    <td>
                        <div class="invoice-info">Facture FAC/'.$id.'</div>
                        <div class="invoice-info">Date de la facture : '.date('d/m/Y').'</div>
                        <div class="invoice-info">Date d\'échéance : '.date('d/m/Y').'</div>
                    </td>
                    <td style="text-align: right">
                        <div class="invoice-info">Patente : 34170797</div>
                        <div class="invoice-info">RC : 294981</div>
                        <div class="invoice-info">IF : 14478748</div>
                    </td>
                </tr>
            </table>
        </div>

        <table>
            <tr>
                <th width="40%">DESCRIPTION</th>
                <th width="15%">QUANTITÉ</th>
                <th width="15%">PRIX UNITAIRE</th>
                <th width="15%">TVA</th>
                <th width="15%">MONTANT</th>
            </tr>
            <tr>
                <td>COMMISSION SMARTBOOKING</td>
                <td>1,00 Unité(e)</td>
                <td>'.number_format($commission_ht, 2).'</td>
                <td>10%</td>
                <td>'.number_format($commission_ht, 2).' DH</td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: right">Sous-total</td>
                <td>'.number_format($commission_ht, 2).' DH</td>
            </tr>
        </table>

        <div class="total-section">
            <div class="bordered">TOTAL HT: '.number_format($commission_ht, 2).' DH</div>
            <div class="bordered">TVA 10%: '.number_format($tva, 2).' DH</div>
            <div class="bordered" style="font-weight: bold">TOTAL COMISSION TTC: '.number_format($total_ttc, 2).' DH</div>
            <div class="bordered" style="font-weight: bold">SOLDE NET: '.number_format($solde_net, 2).' DH</div>
        </div>

        <div style="margin-top: 15px">
            <div>Arrêter la facture à la somme de: '.ucfirst($this->convertNumberToText( $solde_net)).'</div>
            <div style="margin: 10px 0">Règlement Par chèque ou par virement à l’ordre de WEBMANIA</div>
        </div>

        <div class="footer">
            <div>C.N.S.S. : 4672251</div>
            <div>ICE : 001405180000041</div>
            <div>Banque : ATTIJARI WAFABANK - RIB.: 007 780 0002613000000301 63</div>
            <div>Site Internet : www.webmania.ma - Email : contact@webmania.ma - Téléphone: 08 08 53 79 48</div>
        </div>';

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output("invoice_$id.pdf", 'I');
    }
   
}