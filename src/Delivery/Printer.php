<?php


namespace LisDev\Delivery;


use HasRequest;

class Printer
{
    use HasRequest;

    const DEFAULT_SIZE = '85x85';
    const PRINT_URL_PATTERN = 'https://my.novaposhta.ua/orders/%s/orders[]/%s/type/%s/apiKey/%s';

    /**
     * printMarkings method of InternetDocument model.
     *
     * @param array|string $documentRefs Array of Documents IDs
     * @param string $type         (pdf|new_pdf|new_html|old_html|html_link|pdf_link)
     *
     * @return mixed
     */
    public function printMarkings(array $documentRefs, string $type = 'new_html',string $size = self::DEFAULT_SIZE): mixed
    {
        $documentSize = $size === self::DEFAULT_SIZE ? self::DEFAULT_SIZE :'100x100';
        $method = 'printMarking'.$documentSize;
        // If needs link
        if ('html_link' == $type or 'pdf_link' == $type) {
            return $this->printGetLink($method, $documentRefs, $type);
        }
        // If needs data
        return $this->request('InternetDocument', $method, array('DocumentRefs' => $documentRefs, 'Type' => $type));
    }

    /**
     * printDocument method of InternetDocument model.
     *
     * @param array|string $documentRefs Array of Documents IDs
     * @param string $type (pdf|html|html_link|pdf_link)
     *
     * @return mixed
     * @throws \Exception
     */
    public function printDocument(array $documentRefs, string $type = 'html'): mixed
    {
        if ('html_link' == $type or 'pdf_link' == $type) {
            return $this->printGetLink('printDocument', $documentRefs, $type);
        }

        return $this->request('InternetDocument', 'printDocument', array('DocumentRefs' => $documentRefs, 'Type' => $type));
    }

    /**
     * Get only link on internet document for printing.
     *
     * @param string $method Called method of NovaPoshta API
     * @param array $documentRefs Array of Documents IDs
     * @param string $type (html_link|pdf_link)
     *
     * @return mixed
     * @throws \Exception
     */
    protected function printGetLink(string $method, array $documentRefs, string $type): mixed
    {
        $data = sprintf(self::PRINT_URL_PATTERN, $method, implode(',', $documentRefs), str_replace('_link', '', $type), $this->option->getKey());

        return $this->prepare(
            array(
                'success' => true,
                'data' => array($data),
                'errors' => array(),
                'warnings' => array(),
                'info' => array(),
            )
        );
    }

}