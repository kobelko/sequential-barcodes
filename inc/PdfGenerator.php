<?php
/**
 * Generates a PDF with barcodes
 * @author Samuel Kobelkowsky
 * @copyright 2022 (c) Samuel Kobelkowsky
 * @version 0.1
 */

// Include the main TCPDF library (search for installation path).
include_once 'tcpdf/tcpdf.php';

/**
 * Extends TCPDF to generate pages with sequential barcodes
 *
 * @author Samuel Kobelkowsky *
 */
class PdfGenerator extends TCPDF
{

    /**
     * The first number to be printed as bar code
     *
     * @var integer
     */
    var $from;

    /**
     * The last bar code to be printed as bar code
     *
     * @var integer
     */
    var $to;

    /**
     * The number of rows that each page will have
     *
     * @var integer
     */
    const ROWS_PER_PAGE = 12;

    /**
     * The number of columns that each page will have
     *
     * @var integer
     */
    const COLS_PER_PAGE = 5;

    /**
     * The space in mm separating horizontaly each barcode
     *
     * @var integer
     */
    const INTERSPACE_X = 5;

    /**
     * The space in mm separating verticaly each barcode
     *
     * @var integer
     */
    const INTERSPACE_Y = 8;

    /**
     * The maximum number of pages that the resulting PDF will have
     *
     * @var integer
     */
    const MAX_PAGES = 100;

    /**
     * Class constructor
     *
     * @param integer $from
     *            The number of the first barcode
     * @param integer $to
     *            The number of the last barcode
     * @throws Exception
     */
    function __construct($from, $to)
    {
        // Create new PDF document
        parent::__construct('P', PDF_UNIT, 'LETTER', TRUE, 'UTF-8', FALSE);

        $this->from = $from;
        $this->to = $to;

        // Set document information
        $this->SetCreator(PDF_CREATOR);
        $this->SetAuthor('Samuel Kobelkowsky');
        $this->SetTitle('Códigos de barras');

        // set margins
        $this->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

        // Set font
        // dejavusans is a UTF-8 Unicode font, if you only need to
        // print standard ASCII chars, you can use core fonts like
        // helvetica or times to reduce file size.
        $this->SetFont('dejavusans', '', 8, '', true);

        // Remove page decoration
        $this->setPrintHeader(FALSE);
        $this->setPrintFooter(FALSE);

        // Check that we don't exceed the number of allowed pages
        if ($this->getTotalPages() > self::MAX_PAGES) {
            throw new Exception("Too many pages");
        }

        // Create pages and pages full of barcodes :-)
        for ($page = 1; $page <= $this->getTotalPages(); $page ++) {
            $this->addNewPage($page);
        }
    }

    /**
     * Calculate the number of pages containing barcodes, the last page might not be full
     *
     * @return integer
     */
    function getTotalPages()
    {
        return ceil(($this->to - $this->from + 1) / (self::COLS_PER_PAGE * self::ROWS_PER_PAGE));
    }

    /**
     * Calculate the number of barcodes that a particular page contains
     *
     * @param integer $page
     * @return integer
     */
    function getItemsForPage($page)
    {
        if ($page <= 0 || $page > $this->getTotalPages()) {
            return 0;
        }

        if ($page < $this->getTotalPages()) {
            return self::COLS_PER_PAGE * self::ROWS_PER_PAGE;
        }

        return ($this->to - $this->from + 1) - (($page - 1) * self::COLS_PER_PAGE * self::ROWS_PER_PAGE);
    }

    /**
     * Add to the document a new page full of barcodes
     *
     * @param integer $page
     *            The number of page being added. Please, call this method sequentially (1, 2, 3, ...)
     */
    function addNewPage($page)
    {
        // define barcode style
        $style = array(
            // 'position' => '',
            // 'align' => 'C',
            'stretch' => true,
            // 'fitwidth' => false,
            // 'cellfitalign' => '',
            // 'border' => true,
            // 'hpadding' => 'auto',
            // 'vpadding' => 'auto',
            'fgcolor' => array(
                0,
                0,
                0
            ),
            // 'bgcolor' => false, // array(255,255,255),
            'text' => true,
            // 'font' => 'helvetica',
            'fontsize' => 8,
            'stretchtext' => 0
        );

        // This is the initial number of the barcode
        $value = $this->from + (($page - 1) * self::COLS_PER_PAGE * self::ROWS_PER_PAGE);

        // Add a page to the document
        $this->AddPage();

        // Get the page sizes
        $pageWidth = $this->getPageWidth();
        $pageHeight = $this->getPageHeight();
        $margins = $this->getMargins();

        // Calculate the width and height of each barcode
        $width = ($pageWidth - $margins['left'] - $margins['right']) / self::COLS_PER_PAGE;
        $height = ($pageHeight - $margins['top'] - $margins['bottom']) / self::ROWS_PER_PAGE;

        // Minus a space between them
        $width = $width - self::INTERSPACE_X;
        $height = $height - self::INTERSPACE_Y;

        // And don't exceed a maximum size
        $width = min(40, $width);
        $height = min(20, $height);

        // Add each barcode in the page to the document
        for ($j = 0; $j < self::ROWS_PER_PAGE; $j ++) {
            for ($i = 0; $i < self::COLS_PER_PAGE; $i ++) {

                $x = $i * ($width + self::INTERSPACE_X) + $margins['left'];
                $y = $j * ($height + self::INTERSPACE_Y) + $margins['top'];

                $this->write1DBarcode($value, 'CODABAR', $x, $y, $width, $height, 0.3, $style, 'N');
                $this->Ln();

                // Don't exceed the upper limit
                if (++ $value > $this->to)
                    break 2;
            }
        }
    }
}