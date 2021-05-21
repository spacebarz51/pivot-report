<?php

namespace spacebarz51\PivotReport;

/**
 * Class PivotReport
 * To generate html based pivot chart or downloadable CSV
 *
 * Author: Zaidi Mashoti
 * Created: 2021/05/21
 * Modified: 2021/05/21
 */
class PivotReport
{
    public $xColName;
    public $yColName;
    public $totalColName;
    public $pivotData;

    public $colXData = [];
    public $colYData = [];

    /**
     * PivotReport constructor.
     * @param $xColName
     * @param $yColName
     * @param $totalColName
     * @param $data
     */
    public function __construct(string $xColName, string $yColName, string $totalColName, array $data)
    {
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
     * To generate html table with pivot data
     *
     * @param $options
     */
    public function generateHtml($options)
    {
        ?>
        <table border="1" class="<?= $options['class'] ?>">
            <tr>
                <th>&nbsp;</th>
                <?php foreach ($this->colXData as $xValue) : ?>
                    <?= '<th class="header">' . $xValue . '</th>' ?>
                <?php endforeach; ?>
                <th>Total</th>
            </tr>
            <?php foreach ($this->colYData as $yValue) : ?>
                <?= '<tr><td>' . $yValue . '</td>' ?>
                <?php foreach ($this->colXData as $xValue) : ?>
                    <?= '<td class="data">' . $this->getData($xValue, $yValue) . '</td>' ?>
                <?php endforeach; ?>
                <?= '<td class="data">' . $this->totalY($yValue) . '</td></tr>' ?>
            <?php endforeach; ?>
            <tr>
                <th>Total</th>
                <?php foreach ($this->colXData as $xValue) : ?>
                    <th class="data"><?= $this->totalX($xValue) ?></th>
                <?php endforeach; ?>
                <th class="data"><?= $this->grandTotal() ?></th>
            </tr>
        </table>
        <?php
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
            $filename = 'csv_download_' . date('YmdHis') . '.csv';
        }

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
