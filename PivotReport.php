<?php

namespace spacebarz51\PivotReport;

/**
 * Class PivotReport
 * To generate html based pivot chart or downloadable CSV
 *
 * Author: Zaidi Mashoti
 * Created: 2021/05/21
 * Modified: 2021/05/22
 */
class PivotReport
{
    public $title;
    public $xColName;
    public $yColName;
    public $totalColName;
    public $pivotData;

    public $colXData = [];
    public $colYData = [];

    /**
     * PivotReport constructor.
     *
     * @param string $title
     * @param string $xColName
     * @param string $yColName
     * @param string $totalColName
     * @param array $data
     */
    public function __construct(string $title, string $xColName, string $yColName, string $totalColName, array $data)
    {
        $this->title = $title;
        $this->xColName = $xColName;
        $this->yColName = $yColName;
        $this->totalColName = $totalColName;
        $this->pivotData = $data;

        $this->_assignData();
    }

    /**
     * To get data for selected X and Y
     *
     * @param $xValue
     * @param $yValue
     * @return mixed|string
     */
    public function getData($xValue, $yValue)
    {
        foreach ($this->pivotData as $data) {
            if ($data[$this->xColName] == $xValue && $data[$this->yColName] == $yValue) {
                return $data[$this->totalColName];
            }
        }

        return '';
    }

    /**
     * To get total for X
     *
     * @param $value
     * @return int|mixed
     */
    public function totalX($value)
    {
        return $this->_total('xColName', $value);
    }

    /**
     * To get total for Y
     *
     * @param $value
     * @return int|mixed
     */
    public function totalY($value)
    {
        return $this->_total('yColName', $value);
    }

    /**
     * To get grand total for all data
     *
     * @return int|mixed
     */
    public function grandTotal()
    {
        $total = 0;

        foreach ($this->pivotData as $data) {
            $total += $data[$this->totalColName];

        }

        return $total;
    }

    /**
     * To get default CSS style
     *
     * @return string
     */
    public function getDefaultCSS()
    {
        return <<<EOD
<style>
h1 {
    text-align:center;
}
.reportTable {
    width: 100%;
}
.reportTable th, .reportTable td {
    padding: 3px 5px 3px 5px;
}
.reportTable th.header {
    text-align: center;
}
.reportTable th.data, .reportTable td.data {
    text-align: right;
}
.reportTable tr:nth-child(even) {background: #e3e3e3}
.reportTable tr:nth-child(odd) {background: #FFF}
</style>
EOD;
    }

    /**
     * To generate html table with pivot data
     *
     * @param $options
     * @return string
     */
    public function generateHtml($options)
    {
        $html = sprintf('<h1>%s</h1>', $this->title);
        $html .= sprintf('<table border="1" class="%s"><tr><th>&nbsp;</th>', $options['class']);

        foreach ($this->colXData as $xValue) {
            $html .= sprintf('<th class="header">%s</th>', $xValue);
        }

        $html .= '<th>Total</th></tr>';

        foreach ($this->colYData as $yValue) {
            $html .= sprintf('<tr><td>%s</td>', $yValue);

            foreach ($this->colXData as $xValue) {
                $html .= sprintf('<td class="data">%s</td>', $this->getData($xValue, $yValue));
            }

            $html .= sprintf('<td class="data">%s</td></tr>', $this->totalY($yValue));
        }

        $html .= '<tr><th class="bottomRow">Total</th>';

        foreach ($this->colXData as $xValue) {
            $html .= sprintf('<th class="data bottomRow">%s</th>', $this->totalX($xValue));
        }

        $html .= sprintf('<th class="data bottomRow">%s</th>', $this->grandTotal());
        $html .= '</tr></table>';

        return $html;
    }

    /**
     * To generate and download csv data
     *
     * @param null $filename
     * @param string $delimiter
     */
    function generateCsv($filename = null, $delimiter = ',')
    {
        if (empty($filename)) {
            $filename = empty($this->title) ? 'csv_download_' . date('YmdHis') . '.csv' : $this->title . '.csv';
        }

        //clean output
        ob_end_clean();
        header('Content-Type: application/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        $f = fopen('php://output', 'w');

        //create header
        fputcsv($f, array_merge([''], $this->colXData, ['total']), $delimiter);

        //create data
        foreach ($this->colYData as $yValue) {
            $row = [];
            $row[] = $yValue;

            foreach ($this->colXData as $xValue) {
                $row[] = $this->getData($xValue, $yValue);
            }
            $row[] = $this->totalY($yValue);

            fputcsv($f, $row, $delimiter);
        }

        //create summary row
        $summaryRow = [];
        $summaryRow[] = 'Total';

        foreach ($this->colXData as $xValue) {
            $summaryRow[] = $this->totalX($xValue);
        }

        $summaryRow[] = $this->grandTotal();
        fputcsv($f, $summaryRow, $delimiter);
        exit();
    }

    /**
     * To generate as pdf
     *
     * @param null $filename
     */
    function generatePdf($filename = null)
    {
        if (isset($filename) && empty($filename)) {
            $filename = empty($this->title) ? 'pdf_download_' . date('YmdHis') . '.pdf' : $this->title . '.pdf';
        }

        //clean output
        ob_end_clean();
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '";');

        $tcpdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $tcpdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $tcpdf->SetTitle('Bill Collection Letter');

        $tcpdf->SetMargins(10, 10, 10, 10);
        $tcpdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $tcpdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        $tcpdf->setPrintHeader(false);
        $tcpdf->setPrintFooter(false);

        $tcpdf->SetAutoPageBreak(TRUE, 11);

        $tcpdf->AddPage();
        $tcpdf->SetFont('times', '', 10.5);

        $tcpdf->writeHTML($this->generateHtml(['class' => 'reportTable']), true, false, false, false, '');

        if (!empty($filename)) {
            $tcpdf->Output($filename, 'D');
        } else {
            $tcpdf->Output($filename, 'I');
        }

        exit();
    }

    /**
     * To get total based on colname type
     *
     * @param string $colNameType
     * @param $value
     * @return int|mixed
     */
    protected function _total(string $colNameType, $value)
    {
        $total = 0;

        foreach ($this->pivotData as $data) {
            if ($data[$this->{$colNameType}] == $value) {
                $total += $data[$this->totalColName];
            }
        }

        return $total;
    }

    /**
     * To filter data to X and Y group
     */
    protected function _assignData()
    {
        foreach ($this->pivotData as $data) {
            $this->colXData[] = $data[$this->xColName];
            $this->colYData[] = $data[$this->yColName];
        }

        $this->colXData = array_values(array_unique($this->colXData));
        $this->colYData = array_values(array_unique($this->colYData));
    }
}
