<?php
declare(strict_types=1);

use Fpdf\Fpdf as FPDF;
use Sprain\SwissQrBill as QrBill;


require __DIR__ . '/../vendor/autoload.php';

class QRInvoiceUtils
{
    public static function get_qr_bill(
        string $fullname,
        string $street,
        string $postcode,
        string $city,
        string $country,
        string $invoice_number,
        string $currency,
        float $amount
    ): string {

        // This is an example of how to create a qr bill with a reference in SCOR format instead of TYPE_QR.

        // Create a new instance of QrBill, containing default headers with fixed values
        $qrBill = QrBill\QrBill::create();

        // Add creditor information
// Who will receive the payment and to which bank account?
        $qrBill->setCreditor(
            QrBill\DataGroup\Element\CombinedAddress::create(
                'PCN Group di Niccolò Cavallini',
                $GLOBALS['CONFIG']['INVOICES_PCN_ADDRESS']['street'],
                $GLOBALS['CONFIG']['INVOICES_PCN_ADDRESS']['postcode'] . " " . $GLOBALS['CONFIG']['INVOICES_PCN_ADDRESS']['city'],
                $GLOBALS['CONFIG']['INVOICES_PCN_ADDRESS']['country']
            )
        );

        $qrBill->setCreditorInformation(
            QrBill\DataGroup\Element\CreditorInformation::create(
                $GLOBALS['CONFIG']['INVOICES_IBAN']
            )
        );

        // Add debtor information
// Who has to pay the invoice? This part is optional.
//
// Notice how you can use two different styles of addresses: CombinedAddress or StructuredAddress.
// They are interchangeable for creditor as well as debtor.
        $qrBill->setUltimateDebtor(
            QrBill\DataGroup\Element\CombinedAddress::create(
                $fullname,
                $street,
                $postcode . " " . $city,
                $country
            )
        );

        // Add payment amount information
// What amount is to be paid?
        $qrBill->setPaymentAmountInformation(
            QrBill\DataGroup\Element\PaymentAmountInformation::create(
                $currency,
                $amount
            )
        );

        // Add payment reference
// This is what you will need to identify incoming payments.
        $qrBill->setPaymentReference(
            QrBill\DataGroup\Element\PaymentReference::create(
                QrBill\DataGroup\Element\PaymentReference::TYPE_NON,
                # QrBill\Reference\RfCreditorReferenceGenerator::generate(str_replace("/", "A", $invoice_number))
            )
        );

        // Optionally, add some human-readable information about what the bill is for.
        $qrBill->setAdditionalInformation(
            QrBill\DataGroup\Element\AdditionalInformation::create(
                "Fattura PCN Group $invoice_number"
            )
        );

        // Time to output something!
//
// Get the QR code image  …
        $fdpf = new FPDF();
        $fdpf->AddPage();
        $output = new QrBill\PaymentPart\Output\FpdfOutput\FpdfOutput($qrBill, 'it', $fdpf);
        $output->setPrintable(false)->getPaymentPart();
        return $fdpf->Output("S");
    }
}