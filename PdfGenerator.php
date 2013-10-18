<?php
/**
 * Pdf From Generator
 *
 * @package Pdf Form Generator
 * @version 1.0
 * @copyright 2012-2013 Roberto Sobachi
 * @author Roberto Sobachi <roberto.sobachi@me.com>
 */
/*
  $_POST = array (
  "title" => "Mr.",
  "forename" => "John",
  "surname" => "Doe",
  );
 */
 
require_once('fpdf/fpdf.php'); // http://www.fpdf.org/
require_once('fpdi/fpdi.php'); // http://www.setasign.com/products/fpdi/about/


class PdfGenerator
{
    /**
     * Standard text Format
     *
     * @var string
     */

    CONST FORMAT_STD = 'std';
    /**
     * Digits only text format
     *
     * @var string
     */
    CONST FORMAT_DIGIT = 'digit';
    /**
     * Alpha in cells
     *
     * @var string
     */
    CONST FORMAT_IN_CELLS = 'in_cells';
    /**
     * Digits only text format in pair
     *
     * @var string
     */
    CONST FORMAT_PAIR_DIGIT = 'pair_digit';

    /**
     * Boolean text format
     *
     * @var string
     */
    CONST FORMAT_YES_NO = 'yesno';

    /**
     * Value text format
     *
     * @var string
     */
    CONST FORCE_VALUE = 'force_value';

    /**
     * Boolean text format
     *
     * @var float
     */
    CONST FORMAT_YES = 'yes';

    /**
     * Boolean text format
     *
     * @var float
     */
    CONST FORMAT_POSITION_HORIZONTAL = 'horizontal';

    /**
     * Boolean text format
     *
     * @var float
     */
    CONST FORMAT_POSITION_VERTICAL = 'vertical';

    /**
     * PDF width in mm
     *
     * @var float
     */
    public $PDFSizeWidth = 297;

    /**
     * PDF height in mm
     *
     * @var float
     */
    public $PDFSizeHeight = 230;

    /**
     * PDF orientation (Portrait or Landscape)
     *
     * @var char
     */
    public $PDFOrientation = 'L';

    /**
     * PDF font type
     *
     * @var string
     */
    public $PDFFontType = 'Courier';

    /**
     * PDF Font size
     *
     * @var float
     */
    public $PDFFontSize = 8;

    /**
     * Variables to fill the PDF
     *
     * @var array
     */
    public $params = array();

    /**
     * Variables to draw inside a single page
     *
     * @var array
     */
    public $drawArray = array();

    /**
     * If draw a grid
     *
     * @var bool
     */
    public $drawGrid = false;

    /**
     * FPDI Instance
     *
     * @var FPDI
     */
    public $pdf;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->pdf = new FPDI(
                        $this->PDFOrientation,
                        'mm',
                        array($this->PDFSizeWidth, $this->PDFSizeHeight)
        );
    }

    /**
     * Draw a grid of 10mm cell
     *
     * @return void
     */
    private function _drawGrid()
    {
        for ($pos = 10; $pos < 791; $pos = $pos + 10) {
            if (($pos % 100) == 0) {

            } elseif (($pos % 50) == 0) {

            } else {
                $this->pdf->SetDrawColor(200, 200, 200);
                $this->pdf->SetLineWidth(.05);
            }

            $this->pdf->Line(0, $pos, 611, $pos);
            if ($pos < 611) {
                $this->pdf->Line($pos, 0, $pos, 791);
            }
        }
        $this->pdf->SetLineWidth(1);
        $this->pdf->SetDrawColor(0, 0, 0);
        $this->pdf->Rect(0, 0, 612, 792, "D");
    }

    /**
     * Initialize the Pdf from a Pdf Template
     *
     * @param string $formFile Pdf template
     *
     * @return void
     */
    public function drawPdfFromFile($formFile, $post)
    {
        $pagecount = $this->pdf->setSourceFile($formFile);

        for ($i = 0; $i < $pagecount; $i++) {
            $this->_importPage($i + 1);
            $this->_draw($i + 1);
            if ($this->drawGrid) {
                $this->_drawGrid();
            }
            $this->_drawAllVariables($this->params, $post, ($i + 1));
        }
    }

    /**
     * Return the Format of the String to render on PDF
     *
     * @param array $stringObject String object as variable with coordinates
     *
     * @return void
     */
    private function _getStringParam($stringObject, $field)
    {
        return (isset($stringObject[$field])) ? $stringObject[$field] : false;
    }

    /**
     * Cycle and draw all variables into the PDF
     *
     * @param array $vars Predefined variables with x and y coordinates
     * @param array $post String to write on the PDF
     * @param integer $page Page where to draw all variables
     *
     * @return void
     */
    private function _drawAllVariables($vars, $post, $page = 1)
    {
        foreach ($post as $key => $val) {
            if (isset($vars[$key])) {
                if (is_array($vars[$key]) && isset($vars[$key]['page']) && isset($vars[$key]['x']) && isset($vars[$key]['y'])) {
                    $params = array($vars[$key]);
                } else if (is_array($vars[$key])) {
                    $params = $vars[$key];
                }

                $this->_drawVariable($params, $val, $page);
            }
        }
    }

    /**
     * Cycle and draw all variables into the PDF
     *
     * @param array $vars Predefined variables with x and y coordinates
     * @param integer $page Page where to draw all variables
     *
     * @return void
     */
    private function _drawVariable($varArray, $val = '', $page = 1)
    {
        foreach ($varArray as $var) {
            if (is_array($var) && isset($var['page']) && isset($var['x']) && isset($var['y'])) {
                if ($var['page'] == $page) {
		    if (isset($var[self::FORCE_VALUE])) {
			$val = $var[self::FORCE_VALUE];
		    }

                    $this->_drawString(
                            $val, $var['x'], $var['y'], $this->_getStringParam($var, 'format'), $this->_getStringParam($var, 'yes'), $this->_getStringParam($var, 'position'), $this->_getStringParam($var, 'fontsize'), $this->_getStringParam($var, 'gap')
                    );
                }
            }
        }
    }

    /**
     * Import a page from the selected PDF Template
     *
     * @param integer $page Page to Import
     *
     * @return void
     */
    private function _importPage($page)
    {
        $this->pdf->AddPage();
        $tplIdx = $this->pdf->importPage($page);
        $this->pdf->useTemplate($tplIdx);
//$this->_drawGrid();
        unset($tplIdx);
    }

    /**
     * Draw a single string on the current page
     *
     * @param string $text String to write
     * @param integer $x X Coordinate
     * @param integer $y Y Coordinate
     *
     * @return void
     */
    private function _drawString($text, $x, $y, $format = false, $yes = false, $position = false, $fontsize = false, $gap = false)
    {
        if ($fontsize) {
            $fontsize = $this->PDFFontSize - 1;
        } else {
            $fontsize = $this->PDFFontSize;
        }

        $this->pdf->SetFont($this->PDFFontType, '', $fontsize);
        $this->pdf->SetTextColor(0, 0, 0);

        if ($format && ($format == self::FORMAT_DIGIT)) {
            $text = preg_replace('/[^0-9]/', '', $text);

            for ($i = 0; $i < strlen($text); $i++) {
                $this->pdf->SetXY($x, $y);
                $this->pdf->Write(0, $text[$i]);
                $x = $x + 3.8;
            }
        } else if ($format && ($format == self::FORMAT_IN_CELLS)) {
	    $text = strtoupper($text);
            for ($i = 0; $i < strlen($text); $i++) {
                $this->pdf->SetXY($x, $y);
                $this->pdf->Write(0, $text[$i]);
                $x = $x + 3.8;
            }
        } else if ($format && $format == self::FORMAT_PAIR_DIGIT) {
            $index = 0;

            $text = preg_replace('/[^0-9]/', '', $text);

            for ($i = 0; $i < strlen($text); $i++) {
                $this->pdf->SetXY($x, $y);
                $this->pdf->Write(0, $text[$i]);
                $x = $x + 3.75;
                $index++;
                if ($index >= 2) {
                    $x = $x + 4.15;
                    $index = 0;
                }
            }
        } else if ($format && $format == self::FORMAT_YES_NO) {
            if ($yes == false) {
                $yes = 'yes';
            }

            $this->pdf->SetFont($this->PDFFontType, '', $this->PDFFontSize + 2);

            if ($text == $yes) {
                $this->pdf->SetXY($x, $y);
            } else {
                if ($position == self::FORMAT_POSITION_VERTICAL) {
                    $this->pdf->SetXY($x, $y + 5.5);
                } else {
                    if ($gap) {
                        $this->pdf->SetXY($x + $gap, $y);
                    } else {
                        $this->pdf->SetXY($x + 9.5, $y);
                    }
                }
            }
            $this->pdf->Write(0, 'X');
        } else if ($format && $format == self::FORMAT_YES) {
            $text = ($text == 'yes') ? 'X' : '';
            $this->pdf->SetFont($this->PDFFontType, '', $this->PDFFontSize + 2);
            $this->pdf->SetXY($x, $y);
            $this->pdf->Write(0, $text);
        } else {
            $this->pdf->SetXY($x, $y);
            $this->pdf->Write(0, $text);
        }
    }

    /**
     * Draw a rectangle
     *
     * @param int $currentPage Current Page
     *
     * @return void
     */
    private function _draw($currentPage)
    {
        if (count($this->drawArray) > 0) {
            foreach ($this->drawArray as $val) {
                if ($val['page'] == $currentPage) {

                    if (isset($val['fillColor'])) {
                        $this->pdf->SetFillColor($val['fillColor'][0], $val['fillColor'][1], $val['fillColor'][2]);
                    } else {
                        $this->pdf->SetFillColor(255, 255, 255);
                    }

                    $this->pdf->SetDrawColor(0, 0, 0);
                    $this->pdf->Rect($val['x'], $val['y'], $val['w'], $val['h'], $val['type']);
                }
            }
        }
    }

    /**
     * GEnerate and Output the PDF
     *
     * @param string $filename FIlename of the PDF to output
     *
     * @return void
     */
    public function generatePdf($filename)
    {
        $this->pdf->Output($filename, 'D');
    }

}

