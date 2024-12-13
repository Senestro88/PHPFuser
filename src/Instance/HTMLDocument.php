<?php

namespace PHPFuser\Instance;

use Dompdf\Dompdf;
use Dompdf\Options;
use HeimrichHannot\PdfCreator\Concrete\DompdfCreator;
use HeimrichHannot\PdfCreator\Concrete\MpdfCreator;
use HeimrichHannot\PdfCreator\PdfCreatorFactory;
use Mpdf\Mpdf;
use PHPFuser\File;
use PHPFuser\Path;
use \PHPFuser\Utils;
use TCPDF;

/**
 * HTMLDocument class
 *
 * This class provides functionality for dynamically generating HTML documents
 * with specified layout configurations such as dimensions, headers, footers,
 * and structured content in rows and columns.
 * 
 * @author Senestro
 */
class HTMLDocument {
    /**
     * @var array $papers Standard paper sizes with dimensions in millimeters.
     * 
     * The sizes are categorized by series:
     * - A Series: Commonly used for general printing and office use.
     * - B Series: Used for posters, books, and other large formats.
     * - C Series: Used for envelopes.
     * - Common Sizes: Letter, Legal, Tabloid, and Ledger, primarily used in North America.
     */
    private array $papers = array(
        // A Series
        "A0" => array("width" => 841, "height" => 1189),   // 33.1 x 46.8 inches
        "A1" => array("width" => 594, "height" => 841),    // 23.4 x 33.1 inches
        "A2" => array("width" => 420, "height" => 594),    // 16.5 x 23.4 inches
        "A3" => array("width" => 297, "height" => 420),    // 11.7 x 16.5 inches
        "A4" => array("width" => 210, "height" => 297),    // 8.3 x 11.7 inches (default size)
        "A5" => array("width" => 148, "height" => 210),    // 5.8 x 8.3 inches
        "A6" => array("width" => 105, "height" => 148),    // 4.1 x 5.8 inches
        "A7" => array("width" => 74, "height" => 105),     // 2.9 x 4.1 inches
        "A8" => array("width" => 52, "height" => 74),      // 2.0 x 2.9 inches
        "A9" => array("width" => 37, "height" => 52),      // 1.5 x 2.0 inches
        "A10" => array("width" => 26, "height" => 37),     // 1.0 x 1.5 inches
        // B Series
        "B0" => array("width" => 1000, "height" => 1414),  // 39.4 x 55.7 inches
        "B1" => array("width" => 707, "height" => 1000),   // 27.8 x 39.4 inches
        "B2" => array("width" => 500, "height" => 707),    // 19.7 x 27.8 inches
        "B3" => array("width" => 353, "height" => 500),    // 13.9 x 19.7 inches
        "B4" => array("width" => 250, "height" => 353),    // 9.8 x 13.9 inches
        "B5" => array("width" => 176, "height" => 250),    // 6.9 x 9.8 inches
        "B6" => array("width" => 125, "height" => 176),    // 4.9 x 6.9 inches
        "B7" => array("width" => 88, "height" => 125),     // 3.5 x 4.9 inches
        "B8" => array("width" => 62, "height" => 88),      // 2.4 x 3.5 inches
        "B9" => array("width" => 44, "height" => 62),      // 1.7 x 2.4 inches
        "B10" => array("width" => 31, "height" => 44),     // 1.2 x 1.7 inches
        // C Series (Envelope Sizes)
        "C0" => array("width" => 917, "height" => 1297),   // 36.1 x 51.1 inches
        "C1" => array("width" => 648, "height" => 917),    // 25.5 x 36.1 inches
        "C2" => array("width" => 458, "height" => 648),    // 18.0 x 25.5 inches
        "C3" => array("width" => 324, "height" => 458),    // 12.8 x 18.0 inches
        "C4" => array("width" => 229, "height" => 324),    // 9.0 x 12.8 inches
        "C5" => array("width" => 162, "height" => 229),    // 6.4 x 9.0 inches
        "C6" => array("width" => 114, "height" => 162),    // 4.5 x 6.4 inches
        "C7" => array("width" => 81, "height" => 114),     // 3.2 x 4.5 inches
        "C8" => array("width" => 57, "height" => 81),      // 2.2 x 3.2 inches
        "C9" => array("width" => 40, "height" => 57),      // 1.6 x 2.2 inches
        "C10" => array("width" => 28, "height" => 40),     // 1.1 x 1.6 inches
        // Common Paper Sizes
        "Letter" => array("width" => 215.9, "height" => 279.4),  // 8.5 x 11 inches (default in the US)
        "Legal" => array("width" => 215.9, "height" => 355.6),   // 8.5 x 14 inches
        "Tabloid" => array("width" => 279.4, "height" => 431.8), // 11 x 17 inches
        "Ledger" => array("width" => 431.8, "height" => 279.4),  // 17 x 11 inches
    );

    /**
     * Class properties for managing document layout and settings.
     */
    private array $orientations = array("portrait", "landscape"); // Supported page orientations.

    /**
     * @var string $paper The paper size being used (default: "A4").
     */
    private string $paper = "A4";

    /**
     * @var string $orientation The orientation of the document (default: "portrait").
     */
    private string $orientation = "portrait";

    /**
     * @var int $width The width of the document in millimeters, dynamically set based on paper size and orientation.
     */
    private int $width;

    /**
     * @var int $height The height of the document in millimeters, dynamically set based on paper size and orientation.
     */
    private int $height;

    /**
     * @var int $columns The number of columns on each page.
     */
    private int $columns;

    /**
     * @var int $rows The number of rows on each page.
     */
    private int $rows;

    /**
     * @var int $headerHeight The height of the header section in millimeters.
     */
    private int $headerHeight;

    /**
     * @var int $footerHeight The height of the footer section in millimeters (default: 10mm).
     */
    private int $footerHeight = 10;

    /**
     * @var string $headerText The text to display in the header section of each page.
     */
    private string $headerText;

    /**
     * @var array $pages The HTML content of the rendered pages, stored as an array of strings.
     */
    private array $pages = [];

    /**
     * Class constructor for initializing the PDF layout parameters.
     *
     * @param string $paper          The paper size. Defaults to "A4".
     *                               Must match a key in the `$papers` array.
     * @param string $orientation    The paper orientation. Accepts "portrait" or "landscape". Defaults to "portrait".
     * @param int    $columns        The number of columns per page. Defaults to 1.
     * @param int    $rows           The number of rows per page. Defaults to 1.
     * @param int    $headerHeight   The height of the header section in the document (in arbitrary units). Defaults to 10.
     * @param string $headerText     The text to display in the header section of the document. Defaults to an empty string.
     */
    public function __construct(string $paper = "A4", string $orientation = "portrait", int $columns = 1, int $rows = 1, int $headerHeight = 10, string $headerText = "") {
        // Set the paper size. Validates against the $papers array.
        $this->setPaper($paper);
        // Set the orientation of the paper (portrait or landscape).
        $this->setOrientation($orientation);
        // Determine and set the width and height based on the paper size and orientation.
        $this->width = $this->orientation == "portrait" ? $this->papers[$paper]['width'] : $this->papers[$paper]['height'];
        $this->height = $this->orientation == "portrait" ? $this->papers[$paper]['height'] : $this->papers[$paper]['width'];
        // Set the number of columns and rows for the content layout.
        $this->columns = $columns;
        $this->rows = $rows;
        // Set the height of the header section.
        $this->headerHeight = $headerHeight;
        // Set the text to display in the header.
        $this->headerText = $headerText;
    }

    /**
     * Set the paper size for the document.
     *
     * @param string $paper The paper size to set. Must match a key in the `$papers` array.
     *                      If the provided size is not valid, the current paper size is retained.
     */
    public function setPaper(string $paper) {
        // Validate and set the paper size. If invalid, retain the current paper size.
        $this->paper = in_array($paper, array_keys($this->papers)) ? $paper : $this->paper;
    }

    /**
     * Set the orientation for the document.
     *
     * @param string $orientation The orientation to set. Must be a valid key in the `$orientations` array.
     *                            If the provided orientation is not valid, the current orientation is retained.
     */
    public function setOrientation(string $orientation) {
        // Validate and set the orientation. If invalid, retain the current orientation.
        $this->orientation = in_array($orientation, array_keys($this->orientations)) ? $orientation : $this->orientation;
    }

    /**
     * Download the document, either as HTML or as a PDF.
     *
     * @param array  $items     The content items to include in the document.
     * @param bool   $asHtml    Whether to download the document as HTML (default: true). 
     *                          If false, the document is downloaded as a PDF.
     * @param string $htmlTitle The title for the HTML document (optional). 
     *                          Used only when `$asHtml` is true.
     */
    public function download(array $items = array(), bool $asHtml = true, string $htmlTitle = '') {
        // Ensure items are provided before proceeding.
        if (!empty($items)) {
            // Set the items for the document.
            $this->setItems($items);
            // Download the document as HTML or PDF based on the $asHtml flag.
            if ($asHtml) {
                $this->downloadHtml($htmlTitle); // Generate and download the HTML version.
            } else {
                $this->downloadPdf(); // Generate and download the PDF version.
            }
        }
    }


    // PRIVATE METHODS
    /**
     * Get the temporary directory path for storing HTML documents.
     *
     * @return string The path to the temporary directory with proper directory separators.
     */
    private function getTempDir(): string {
        // Arrange and ensure proper directory separators for the temporary directory path.
        return Path::insert_dir_separator(
            Path::arrange_dir_separators(
                PHPFUSER['DIRECTORIES']['DATA']
                    . DIRECTORY_SEPARATOR
                    . 'htmldocument'
                    . DIRECTORY_SEPARATOR
                    . 'temp'
            )
        );
    }

    /**
     * Set the content items for the document, organizing them into pages based on the layout.
     *
     * @param array $items The content items to include in the document.
     *
     * @throws \Exception If the calculated row height is zero or negative due to layout settings.
     */
    private function setItems(array $items) {
        // Calculate the usable height for content by subtracting header and footer heights.
        $usableHeight = $this->height - ($this->headerHeight + $this->footerHeight);
        // Calculate the height of each row based on the number of rows specified.
        $rowHeight = $usableHeight / $this->rows;
        // Ensure the row height is valid. If not, throw an exception.
        if ($rowHeight <= 0) {
            throw new \Exception('Row height cannot be zero or negative. Check header and footer dimensions.');
        } else {
            // Calculate the number of items per page based on columns and rows.
            $itemsPerPage = $this->columns * $this->rows;
            // Determine the total number of pages required.
            $totalPages = ceil(count($items) / $itemsPerPage);
            // Organize items into pages.
            for ($page = 1; $page <= $totalPages; $page++) {
                // Extract the items for the current page.
                $pageItems = array_slice(
                    $items,
                    ($page - 1) * $itemsPerPage,
                    $itemsPerPage
                );
                // Render the page and add it to the pages array.
                $this->pages[] = $this->renderPage($page, $totalPages, $pageItems, $rowHeight);
            }
        }
    }

    /**
     * Render a single page of the document with the specified items and layout settings.
     *
     * @param int $currentPage The current page number being rendered.
     * @param int $totalPages The total number of pages in the document.
     * @param array $items The items to display on the current page.
     * @param float $rowHeight The height of each row in millimeters.
     * @return string The HTML content for the rendered page.
     */
    private function renderPage($currentPage, $totalPages, $items, $rowHeight): string {
        // Initialize the page container with specified dimensions and class.
        $html = '<div style="width:' . $this->width . 'mm; height:' . $this->height . 'mm;" class="page">';
        // Header section
        $html .= '<div style="height:' . $this->headerHeight . 'mm;" class ="header">';
        $html .= htmlspecialchars($this->headerText); // Add sanitized header text
        $html .= '</div>';
        // Body section
        $usableHeight = $this->height - ($this->headerHeight + $this->footerHeight);
        $html .= '<div style="height:' . $usableHeight . 'mm;" class="container">';
        $html .= '<ul>';
        // Iterate through items to generate list elements
        foreach ($items as $item) {
            $html .= '<li style="width:' . (100 / $this->columns) . '%; height:' . $rowHeight . 'mm;">';
            $html .= $item;
            $html .= '</li>';
        }
        $html .= '</ul>';
        $html .= '</div>';
        // Footer section
        $html .= '<div class="footer" style="height:' . $this->footerHeight . 'mm;">';
        $html .= '<div class="page-number">Page ' . $currentPage . ' / ' . $totalPages . '</div>';
        $html .= '</div>';
        // Close the page container
        $html .= '</div>';
        // Return the complete HTML for the page
        return $html;
    }

    /**
     * Combine all rendered HTML pages into a single string.
     *
     * @return string The concatenated HTML content of all pages.
     */
    private function getHtmlPages(): string {
        // Combine all pages stored in the $this->pages array into a single HTML string.
        return \implode("", \array_values($this->pages));
    }

    /**
     * Generate and download the document as a PDF file.
     *
     * This method uses the Dompdf library to render HTML content into a PDF and download it.
     */
    private function downloadPdf() {
        // Get the temporary directory for Dompdf to store files during processing.
        $tempDir = $this->getTempDir();
        // Generate the complete HTML for the document.
        $html = $this->geHtml("");
        // Retrieve the file names for the downloaded PDF.
        $downloadNames = $this->getDownloadNames();
        // Configure options for the Dompdf instance.
        $options = new Options();
        $options->setIsFontSubsettingEnabled(true); // Enable font subsetting for reduced file size.
        $options->set('isHtml5ParserEnabled', true); // Enable HTML5 parsing.
        $options->setIsJavascriptEnabled(true); // Allow JavaScript execution in the HTML.
        $options->setIsPhpEnabled(true); // Allow PHP execution in the HTML.
        $options->setIsRemoteEnabled(true); // Enable fetching remote resources (e.g., images, CSS).
        $options->setTempDir($tempDir); // Set the temporary directory for Dompdf.
        // Create a new Dompdf instance with the configured options.
        $dompdf = new Dompdf($options);
        // Load the generated HTML into Dompdf.
        $dompdf->loadHtml($html);
        // Set the paper size and orientation for the PDF.
        $dompdf->setPaper([$this->width, $this->height], $this->orientation);
        // Render the HTML into PDF format.
        $dompdf->render();
        // Stream the generated PDF to the user for download with the specified file name.
        $dompdf->stream($downloadNames['pdf'], ['Attachment' => true]);
    }

    /**
     * Generate the complete HTML document structure with a given title.
     *
     * @param string $title The title of the HTML document.
     * @return string The generated HTML document as a string.
     */
    private function geHtml(string $title): string {
        // Generate the HTML document structure with inline styles and meta tags.
        $html = '<!DOCTYPE html>
            <html xmlns:fb="http://www.facebook.com/2008/fbml" charset="utf-8" lang="en-US" xmlns="http://www.w3.org/1999/xhtml" xmlns:b="http://www.google.com/2005/gml/b" xmlns:data="http://www.google.com/2005/gml/data" xmlns:expr="http://www.google.com/2005/gml/expr">
            <head>
                <title>' . $title . '</title>
                <style type="text/css">*, *:after, *:before {padding: 0px; margin: 0; outline: none; -moz-box-sizing: border-box; -webkit-box-sizing: border-box; box-sizing: border-box } * {-webkit-font-smoothing: antialiased; font-size: 100% } html {overflow: auto; width: 100%; height: auto; -webkit-text-size-adjust: auto; -moz-text-size-adjust: auto; background: #e9ecef; color: #000; text-align: center; margin: 0; padding: 0; word-wrap: break-word; overflow-wrap: break-word; } .clearfix:after, .clearfix:before {display: block; clear: both; content: ""; } body {color: #000; background: linear-gradient(74deg, rgba(241, 241, 241, 1) 0, rgba(241, 241, 241, 1) 25%, rgba(229, 226, 226, 1) 25%, rgba(229, 226, 226, 1) 75%, rgba(241, 241, 241, 1) 75%, rgba(241, 241, 241, 1) 100%); background: -webkit-linear-gradient(74deg, rgba(241, 241, 241, 1) 0, rgba(241, 241, 241, 1) 25%, rgba(229, 226, 226, 1) 25%, rgba(229, 226, 226, 1) 75%, rgba(241, 241, 241, 1) 75%, rgba(241, 241, 241, 1) 100%); background: -o-linear-gradient(74deg, rgba(241, 241, 241, 1) 0, rgba(241, 241, 241, 1) 25%, rgba(229, 226, 226, 1) 25%, rgba(229, 226, 226, 1) 75%, rgba(241, 241, 241, 1) 75%, rgba(241, 241, 241, 1) 100%); background: -moz-linear-gradient(74deg, rgba(241, 241, 241, 1) 0, rgba(241, 241, 241, 1) 25%, rgba(229, 226, 226, 1) 25%, rgba(229, 226, 226, 1) 75%, rgba(241, 241, 241, 1) 75%, rgba(241, 241, 241, 1) 100%); background: -ms-linear-gradient(74deg, rgba(241, 241, 241, 1) 0, rgba(241, 241, 241, 1) 25%, rgba(229, 226, 226, 1) 25%, rgba(229, 226, 226, 1) 75%, rgba(241, 241, 241, 1) 75%, rgba(241, 241, 241, 1) 100%); font-family: monospace, sans-serif; position: relative; appearance: none; -webkit-appearance: none; -moz-appearance: none; width: 100%; height: auto; text-align: center; word-wrap: break-word; margin: 0; display: block; padding: 0; } @media print {body {margin: 0; padding: 0; } @page {margin: 0; } } .page {display: block; padding: 0; margin: 0 auto; position: relative; border: 2px solid black; overflow: hidden; page-break-inside: avoid; page-break-after: always; } .page .header {overflow: hidden; border-bottom: 2px solid black; display: flex; align-items: center; justify-content: center; flex-wrap: nowrap; flex-direction: row; font-family: system-ui, cursive; font-size: 100%; color: maroon; font-weight: bold; letter-spacing: 1px; } .page .container {position: relative; width: 100%; display: block; overflow: hidden; } .page .container ul {list-style: none; padding: 0; margin: 0; display: flex; flex-wrap: wrap; } .page .container ul li {border: 2px solid #e5dede; display: flex; align-items: center; justify-content: center; flex-wrap: nowrap; flex-direction: column;} .page .footer {text-align: left; border-top: 2px solid black; display: flex; flex-wrap: nowrap; justify-content: flex-start; align-items: center; font-weight: normal; font-style: italic; font-size: 100%; padding: 0px 10px; } .page .footer .page-number {font-weight: bold; font-style: inherit; font-size: 85%; padding: 0px;}</style>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=yes" />
                <meta name="theme-color" content="#312C2C">
                <meta name="msapplication-navbutton-color" content="#312C2C">
                <meta name="apple-mobile-web-app-capable" content="yes">
                <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
                <meta http-equiv="content-type" content="text/html;charset=utf-8" />
            </head>
            <body>
                ' . $this->getHtmlPages() . '
            </body>
        </html>';
        return $html;
    }

    /**
     * Generate and prompt a download for the HTML version of the document.
     *
     * @param string $title The title of the HTML document.
     */
    private function downloadHtml(string $title) {
        // Generate the HTML content for the document.
        $html = $this->geHtml($title);
        // Get the names for the downloadable files.
        $downloadNames = $this->getDownloadNames();
        // Use a utility function to trigger the HTML download.
        Utils::downloadContent($html, $downloadNames['html']);
    }

    /**
     * Generate unique file names for the downloadable HTML and PDF files.
     *
     * @return array An associative array containing "pdf" and "html" file names.
     */
    private function getDownloadNames(): array {
        // Generate a unique base key for the file names.
        $basenameKey = Utils::randUnique("key");
        // Construct the HTML and PDF file names.
        $htmlName = $basenameKey . ".html";
        $pdfName = $basenameKey . ".pdf";
        return array("pdf" => $pdfName, "html" => $htmlName);
    }
}
