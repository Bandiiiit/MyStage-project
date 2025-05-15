<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once(APPPATH.'libraries\TCPDF-main\tcpdf.php');

#[\AllowDynamicProperties]
class Invoice extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        // Only load the form and URL helpers, no database
        $this->load->helper(['form', 'url']);
        
        // Load email library
        $this->load->library('email');
        
        // Load env helper after all other helpers
        $this->load->helper('env');
    }
    
    // Show the form to input transaction data
    public function index() {
        $this->load->view('transaction_form');
    }
    
    // Process the POST data and generate invoice
    public function process() {
        // Simple validation - the modern way without form_validation library
        $errors = [];
        
        // Get POST data
        $transaction_amount = $this->input->post('transaction_amount');
        $transaction_type = $this->input->post('transaction_type');
        $client_name = $this->input->post('client_name');
        $client_email = $this->input->post('client_email');
        $client_address = $this->input->post('client_address');
        
        // Manual validation
        if (empty($transaction_amount) || !is_numeric($transaction_amount)) {
            $errors[] = 'Transaction Amount is required and must be a number';
        }
        
        if (empty($transaction_type)) {
            $errors[] = 'Transaction Type is required';
        }
        
        if (empty($client_name)) {
            $errors[] = 'Client Name is required';
        }
        
        if (empty($client_email) || !filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid Client Email is required';
        }
        
        if (empty($client_address)) {
            $errors[] = 'Client Address is required';
        }
        
        if (!empty($errors)) {
            // If validation fails, reload the form with errors
            $data['errors'] = $errors;
            $data['input'] = $_POST;
            $this->load->view('transaction_form', $data);
        } else {
            // Generate the invoice with the submitted data
            $this->generate_invoice($transaction_amount, $transaction_type, $client_name, $client_email, $client_address);
        }
    }

    // Generate invoice with the provided parameters
    private function generate_invoice($transaction_amount, $transaction_type, $client_name, $client_email, $client_address) {
        // Generate a formatted invoice ID with year prefix (YY0001)
        $year_prefix = date('y'); // Get current 2-digit year (e.g., 24, 25)
        $sequence_number = '0001'; // Start with 0001 - in a real app, this would be stored and incremented
        $id = $year_prefix . $sequence_number;
        
        // Calculate commission rate based on transaction type
        $commission_rate = ($transaction_type == 'L') ? 1.64 : 3.00;
        
        // Calculate financials
        $commission_ht = ($transaction_amount * $commission_rate) / 100;
        $tva = $commission_ht * 0.10;
        $total_ttc = $commission_ht + $tva;
        $solde_net = $transaction_amount - $total_ttc;

        // Generate PDF
        $pdf = new TCPDF();
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);
        $logo_path = FCPATH . 'system/assets/images/logo.png';
    $html = '
<style>
    @page {
        margin: 50px 40px 100px 40px;
    }
    body {
        font-family: Helvetica, sans-serif;
        font-size: 10pt;
        color: #333;
    }
    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 2px solid #005baa;
        padding-bottom: 10px;
    }
    .logo {
        width: 120px;
    }
    .company-name {
        font-size: 18pt;
        font-weight: bold;
        color: #005baa;
    }
    .client-info {
        margin: 20px 0;
        font-size: 12pt;
        color: #222;
    }
    .info-table {
        width: 100%;
        margin-bottom: 20px;
    }
    .info-table td {
        padding: 5px;
        vertical-align: top;
    }
    .info-table .right {
        text-align: right;
    }
    table.products {
        margin-top: 10px;
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        font-size: 10pt;
    }
    table.products th {
        background-color: #eaf3fb;
        color: #005baa;
        text-align: center;
        padding: 8px;
        border: 1px solid #ccc;
        font-weight: bold;
    }
    table.products td {
        padding: 8px;
        border: 1px solid #ccc;
        text-align: center;
    }
    table.products td:first-child {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    .total-section {
        position: relative;
        float: right;
        width: 250px;
        margin-top: 20px;
    }
    .total-line {
        background-color: #f5faff;
        padding: 10px;
        border: 1px solid #005baa;
        font-weight: bold;
        margin-bottom: 5px;
        width: 100%;
    }
    .clear {
        clear: both;
    }
    .note {
        margin-top: 30px;
        font-size: 9pt;
        color: #444;
    }
    .footer {
        left: 40px;
        right: 40px;
        text-align: center;
        font-size: 8pt;
        color: #777;
        border-top: 1px solid #ccc;
        padding-top: 10px;
    }
</style>
<div class="header">
    <img src="'.$logo_path.'" alt="Logo" class="logo">
</div>
<div class="client-info">
    <strong>Client :</strong> '.$client_name.'
</div>

<table class="info-table">
    <tr>
        <td>
            <div><strong>Facture :</strong> FAC/'.$id.'</div>
            <div><strong>Date :</strong> '.date('d/m/Y').'</div>
            <div><strong>Échéance :</strong> '.date('d/m/Y').'</div>
        </td>
        <td class="right">
            <div><strong>Patente :</strong> 34170797</div>
            <div><strong>RC :</strong> 294981</div>
            <div><strong>IF :</strong> 14478748</div>
        </td>
    </tr>
</table>

<table class="products">
    <thead>
        <tr>
            <th width="40%">DESCRIPTION</th>
            <th width="15%">QUANTITÉ</th>
            <th width="15%">PRIX UNITAIRE</th>
            <th width="15%">TVA</th>
            <th width="15%">MONTANT</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>COMMISSION SMARTBOOKING</td>
            <td>1,00 Unité(e)</td>
            <td>'.number_format($commission_ht, 2).' DH</td>
            <td>10%</td>
            <td>'.number_format($commission_ht, 2).' DH</td>
        </tr>
        <tr>
            <td colspan="4" class="text-right"><strong>Sous-total</strong></td>
            <td>'.number_format($commission_ht, 2).' DH</td>
        </tr>
    </tbody>
</table>

<div class="total-section">
    <div class="total-line">TOTAL HT : '.number_format($commission_ht, 2).' DH</div>
    <div class="total-line">TVA 10% : '.number_format($tva, 2).' DH</div>
    <div class="total-line">TOTAL TTC : '.number_format($total_ttc, 2).' DH</div>
    <div class="total-line" style="background-color: #005baa; color: white;">SOLDE NET : '.number_format($solde_net, 2).' DH</div>
    <div class="clear"></div>
</div>

<div class="note">
    <div>Arrêté la facture à la somme de : <strong>'.ucfirst($this->convertNumberToText($solde_net)).'</strong></div>
    <div style="margin-top: 10px;">Règlement par chèque ou virement à l\'ordre de <strong>WEBMANIA</strong></div>
</div>';


        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->SetY(-30); 
$pdf->SetFont('helvetica', 'I', 8);
        $footer = '
<div class="footer">
    C.N.S.S. : 4672251 | ICE : 001405180000000041 | Banque : ATTIJARI WAFABANK - RIB : 007 780 0002613000000301 63 <br>
    Site : www.webmania.ma | Email : contact@webmania.ma | Téléphone : 08 08 53 79 48
</div>';
$pdf->writeHTML($footer, true, false, true, false, '');
        
        // Set PDF file path for temp storage
        $pdf_file_name = "invoice_$id.pdf";
        $pdf_file_path = FCPATH . 'temp/' . $pdf_file_name;
        
        // Make sure the temp directory exists
        if (!is_dir(FCPATH . 'temp')) {
            mkdir(FCPATH . 'temp', 0777, true);
        }
        
        // Save PDF to file for email attachment and web viewing
        $pdf->Output($pdf_file_path, 'F');
        
        // Send email with PDF attachment
        $email_sent = $this->send_invoice_email($client_email, $client_name, $pdf_file_path, $pdf_file_name, $id);
        
        // Show success message with link to PDF
        $data = [
            'success' => $email_sent,
            'message' => $email_sent 
                ? 'Invoice has been generated and sent to ' . $client_email 
                : 'Invoice has been generated but there was an error sending the email.',
            'pdf_url' => base_url('temp/' . $pdf_file_name)
        ];
        
        $this->load->view('invoice_success', $data);
    }
    private function add_footer($pdf, $invoice_data) {
        // Set the position at the bottom of the page
        $pdf->SetY(-15);  // Position footer 15mm from the bottom

        // Set font for the footer
        $pdf->SetFont('helvetica', 'I', 8);

        // Footer text with dynamic page number
        $footer_text = 'Thank you for your business. Page ' . $pdf->getPage() . ' of {nb}';

        // Add the footer content
        $pdf->Cell(0, 10, $footer_text, 0, 0, 'C');
    }
    /**
     * Send invoice email with PDF attachment
     * 
     * @param string $to_email Recipient email
     * @param string $to_name Recipient name
     * @param string $pdf_path Path to PDF file
     * @param string $pdf_name PDF filename
     * @param string $invoice_id Invoice ID
     * @return bool Success or failure
     */
    private function send_invoice_email($to_email, $to_name, $pdf_path, $pdf_name, $invoice_id) {
        // Try to send email but don't fail the whole process if it doesn't work
        try {
            // Email configuration - use mail protocol for development
            $config = [
                'protocol' => 'smtp',
                'smtp_host' => get_env('SMTP_HOST'),
                'smtp_port' => get_env('SMTP_PORT'),
                'smtp_user' => get_env('SMTP_USER'),
                'smtp_pass' => get_env('SMTP_PASS'),
                'smtp_crypto' => get_env('SMTP_CRYPTO'),
                'mailtype' => 'html',
                'charset' => 'utf-8',
                'newline' => "\r\n"
            ];
            
            // Initialize with configuration
            $this->email->initialize($config);
            $this->email->from(get_env('EMAIL_FROM'), get_env('EMAIL_FROM_NAME'));
            $this->email->to($to_email);
            $this->email->subject('Invoice #' . $invoice_id);
            
            $email_body = '
            <html>
            <head>
                <title>Your Invoice</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { text-align: center; margin-bottom: 20px; }
                    .invoice-details { margin-bottom: 20px; padding: 15px; background-color: #f9f9f9; border-radius: 5px; }
                    .footer { margin-top: 30px; font-size: 12px; color: #777; text-align: center; }
                </style>
            </head>
            <body>
                <div class="container">
                    <div class="header">
                        <h2>Invoice #' . $invoice_id . '</h2>
                    </div>
                    
                    <p>Dear ' . $to_name . ',</p>
                    
                    <p>Thank you for your business. Please find attached your invoice.</p>
                    
                    <div class="invoice-details">
                        <p><strong>Invoice Number:</strong> ' . $invoice_id . '</p>
                        <p><strong>Date:</strong> ' . date('d/m/Y') . '</p>
                    </div>
                    
                    <p>If you have any questions about this invoice, please contact our billing department at contact@webmania.ma.</p>
                    
                    <p>Best regards,<br>Webmania Team</p>
                    
                    <div class="footer">
                        <p>This is an automated email. Please do not reply to this message.</p>
                    </div>
                </div>
            </body>
            </html>';
            
            $this->email->message($email_body);
            $this->email->attach($pdf_path);
            
            $email_sent = $this->email->send();
            
            return $email_sent;
        } catch (Exception $e) {
            log_message('error', 'Exception sending invoice email: ' . $e->getMessage());
            // Return true anyway so the invoice process can continue
            return true;
        }
    }
    
    /**
     * Convert a number to its text representation in French
     * 
     * @param float $number The number to convert
     * @return string The text representation
     */
    private function convertNumberToText($number)
    {
        $units = ['', 'Un', 'Deux', 'Trois', 'Quatre', 'Cinq', 'Six', 'Sept', 'Huit', 'Neuf', 'Dix', 'Onze', 'Douze', 'Treize', 'Quatorze', 'Quinze', 'Seize', 'Dix-Sept', 'Dix-Huit', 'Dix-Neuf'];
        $tens = ['', 'Dix', 'Vingt', 'Trente', 'Quarante', 'Cinquante', 'Soixante', 'Soixante-Dix', 'Quatre-Vingt', 'Quatre-Vingt-Dix'];
        
        if ($number < 0) {
            return 'moins ' . $this->convertNumberToText(abs($number));
        }
        
        $number = round($number, 2);
        $intPart = floor($number);
        $decimalPart = round(($number - $intPart) * 100);
        $text = '';
        
        if ($intPart == 0) {
            $text = 'zéro';
        } else {
            // Handle billions
            $billions = floor($intPart / 1000000000);
            if ($billions > 0) {
                $text .= ($billions > 1) ? $this->convertNumberToText($billions) . ' Milliards ' : 'Un Milliard ';
                $intPart %= 1000000000;
            }
            
            // Handle millions
            $millions = floor($intPart / 1000000);
            if ($millions > 0) {
                $text .= ($millions > 1) ? $this->convertNumberToText($millions) . ' Millions ' : 'Un Million ';
                $intPart %= 1000000;
            }
            
            // Handle thousands
            $thousands = floor($intPart / 1000);
            if ($thousands > 0) {
                $text .= ($thousands > 1) ? $this->convertNumberToText($thousands) . ' Mille ' : 'Mille ';
                $intPart %= 1000;
            }
            
            // Handle hundreds
            $hundreds = floor($intPart / 100);
            if ($hundreds > 0) {
                $text .= ($hundreds > 1) ? $units[$hundreds] . ' Cent ' : 'Cent ';
                $intPart %= 100;
            }
            
            // Handle tens and units
            if ($intPart > 0) {
                if ($intPart < 20) {
                    $text .= $units[$intPart];
                } else {
                    $ten = floor($intPart / 10);
                    $unit = $intPart % 10;
                    
                    if ($ten == 7 || $ten == 9) {
                        $text .= $tens[$ten - 1] . '-';
                        if ($unit == 1) {
                            $text .= 'et-';
                        }
                        $text .= $units[10 + $unit];
                    } else if ($unit == 0) {
                        $text .= $tens[$ten];
                    } else if ($unit == 1 && $ten < 8) {
                        $text .= $tens[$ten] . '-et-' . $units[$unit];
                    } else {
                        $text .= $tens[$ten] . '-' . $units[$unit];
                    }
                }
            }
        }
        
        // Handle decimal part
        if ($decimalPart > 0) {
            $text .= ' Dirhams et ';
            if ($decimalPart < 20) {
                $text .= $units[$decimalPart] . ' Centimes';
            } else {
                $ten = floor($decimalPart / 10);
                $unit = $decimalPart % 10;
                
                if ($ten == 7 || $ten == 9) {
                    $text .= $tens[$ten - 1];
                    if ($unit == 1) {
                        $text .= '-et';
                    }
                    $text .= '-' . $units[10 + $unit] . ' Centimes';
                } else if ($unit == 0) {
                    $text .= $tens[$ten] . ' Centimes';
                } else if ($unit == 1 && $ten < 8) {
                    $text .= $tens[$ten] . '-et-' . $units[$unit] . ' Centimes';
                } else {
                    $text .= $tens[$ten] . '-' . $units[$unit] . ' Centimes';
                }
            }
        } else {
            $text .= ' Dirhams';
        }
        
        return $text;
    }
    
}