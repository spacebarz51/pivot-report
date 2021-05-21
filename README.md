# pivot-report

## Usage

---
Create SQL in format as below:

```
select
    budget_code,    <-- will be used for column header
    payment_code,   <-- will be used for row header
    sum(qty)        <-- will be used for data
from 
    table_name
group by 
    budget_code, payment_code   <-- compulsory
order by 
    budget_code, payment_code   <-- if needed
```

## Syntax
    
---

#### PivotReport object instantiation

```
$pivotReport = new PivotReport(
    <title of report>, 
    <column header field name>, 
    <row header field name>, 
    <data field name>, 
    <the data in array>
);
```

## Example

---

#### To display as HTML table

```
$pivotReport = new PivotReport(
    'Budget Report', 'budget_code', 'payment_code', 'total', $pivotData);
echo $pivotReport->getDefaultCSS();
echo $pivotReport->generateHtml(['class' => 'reportTable']);
```

#### To download as CSV
```
$pivotReport = new PivotReport('Budget Report', 'budget_code', 'payment_code', 'total', $pivotData);
$pivotReport->generateCsv();
```

#### To display as PDF
```
$pivotReport = new PivotReport('Budget Report', 'budget_code', 'payment_code', 'total', $pivotData);
$pivotReport->generatePdf();
```

#### To download as PDF
```
$pivotReport = new PivotReport('Budget Report', 'budget_code', 'payment_code', 'total', $pivotData);
$pivotReport->generatePdf('test.pdf');
```