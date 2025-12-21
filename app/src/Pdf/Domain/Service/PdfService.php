<?php

namespace App\Pdf\Domain\Service;

use Dompdf\Dompdf;

class PdfService
{
    public function createPdf(array $params): string
    {
        $data = file_get_contents($params['url']);

        $dompdf = new Dompdf();

        $options = $dompdf->getOptions();
        $options->set('defaultFont', 'roboto');
        $dompdf->setOptions($options)->loadHtml($data, 'UTF-8');

        $dompdf->setPaper('A4')->render();

        $fileName = sha1($params['url']);

        file_put_contents('/app/var/files/' . $fileName . '.pdf', $dompdf->output());

        return $fileName . '.pdf';
    }
}