<?php namespace Backend\Models;

use File;
use Lang;
use Model;
use Response;
use League\Csv\Writer as CsvWriter;
use League\Csv\EscapeFormula as CsvEscapeFormula;
use ApplicationException;
use SplTempFileObject;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * Model used for exporting data
 *
 * @package winter\wn-backend-module
 * @author Alexey Bobkov, Samuel Georges
 */
abstract class ExportXlsxModel extends Model
{
    /**
     * Called when data is being exported.
     * The return value should be an array in the format of:
     *
     *   [
     *       'db_name1' => 'Some attribute value',
     *       'db_name2' => 'Another attribute value'
     *   ],
     *   [...]
     *
     */
    abstract public function exportData($columns, $sessionKey = null);

    /**
     * Export data based on column names and labels.
     * The $columns array should be in the format of:
     *
     *   [
     *       'db_name1' => 'Column label',
     *       'db_name2' => 'Another label',
     *       ...
     *   ]
     *
     */
    public function export($columns, $options)
    {
        $sessionKey = array_get($options, 'sessionKey');
        $data = $this->exportData(array_keys($columns), $sessionKey);
        
        // التحقق من نوع التصدير
        $format = array_get($options, 'format', 'csv');
        
        if ($format === 'xlsx') {
            return $this->processExportXlsx($columns, $data, $options);
        }
        
        return $this->processExportData($columns, $data, $options);
    }

    /**
     * Download a previously compiled export file.
     * @return void
     */
    public function download($name, $outputName = null)
    {
        if (!preg_match('/^oc[0-9a-z]*$/i', $name)) {
            throw new ApplicationException(Lang::get('backend::lang.import_export.file_not_found_error'));
        }

        // التحقق من امتداد الملف
        $extension = pathinfo($outputName, PATHINFO_EXTENSION);
        $isXlsx = ($extension === 'xlsx');
        
        $filePath = temp_path() . '/' . $name;
        if (!file_exists($filePath)) {
            throw new ApplicationException(Lang::get('backend::lang.import_export.file_not_found_error'));
        }

        // تحديد نوع المحتوى حسب الامتداد
        $contentType = $isXlsx 
            ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            : 'text/csv';

        return Response::download($filePath, $outputName, ['Content-Type' => $contentType])
                       ->deleteFileAfterSend(true);
    }

    /**
     * Converts a data collection to a CSV file.
     */
    protected function processExportData($columns, $results, $options)
    {
        // ... الكود الأصلي للـ CSV ...
        $defaultOptions = [
            'firstRowTitles' => true,
            'useOutput' => false,
            'fileName' => 'export.csv',
            'delimiter' => null,
            'enclosure' => null,
            'escape' => null
        ];

        $options = array_merge($defaultOptions, $options);
        $columns = $this->exportExtendColumns($columns);

        $csv = CsvWriter::createFromFileObject(new SplTempFileObject);
        $csv->setOutputBOM(CsvWriter::BOM_UTF8);

        if ($options['delimiter'] !== null) {
            $csv->setDelimiter($options['delimiter']);
        }
        if ($options['enclosure'] !== null) {
            $csv->setEnclosure($options['enclosure']);
        }
        if ($options['escape'] !== null) {
            $csv->setEscape($options['escape']);
        }
        $csv->addFormatter(new CsvEscapeFormula());

        if ($options['firstRowTitles']) {
            $headers = $this->getColumnHeaders($columns);
            $csv->insertOne($headers);
        }

        foreach ($results as $result) {
            $data = $this->matchDataToColumns($result, $columns);
            $csv->insertOne($data);
        }

        if ($options['useOutput']) {
            $csv->output($options['fileName']);
        }

        $csvName = uniqid('oc');
        $csvPath = temp_path().'/'.$csvName;
        $output = $csv->__toString();
        File::put($csvPath, $output);

        return $csvName;
    }

    /**
     * Converts a data collection to an Excel XLSX file.
     */
    protected function processExportXlsx($columns, $results, $options)
    {
        // التحقق من وجود البيانات
        if (!$results) {
            throw new ApplicationException(Lang::get('backend::lang.import_export.empty_error'));
        }

        // خيارات التصدير
        $defaultOptions = [
            'firstRowTitles' => true,
            'useOutput' => false,
            'fileName' => 'export.xlsx',
            'sheetTitle' => 'Export',
            'autoSize' => true,
            'rtl' => true,
        ];

        $options = array_merge($defaultOptions, $options);
        $columns = $this->exportExtendColumns($columns);

        // إنشاء Spreadsheet جديد
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // تعيين عنوان الورقة
        $sheet->setTitle($options['sheetTitle']);

        // إضافة العناوين
        $row = 1;
        $col = 'A';
        
        if ($options['firstRowTitles']) {
            $headers = $this->getColumnHeaders($columns);
            foreach ($headers as $header) {
                $sheet->setCellValue($col . $row, $header);
                $col++;
            }
            
            // تنسيق رأس الجدول
            $lastCol = chr(ord('A') + count($headers) - 1);
            $headerRange = 'A' . $row . ':' . $lastCol . $row;
            
            $sheet->getStyle($headerRange)->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle($headerRange)->getFont()->getColor()->setARGB('FFFFFFFF');
            $sheet->getStyle($headerRange)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setARGB('FF2C3E50');
            $sheet->getStyle($headerRange)->getAlignment()
                  ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                  ->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle($headerRange)->getBorders()
                  ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            
            $row++;
        }

        // إضافة البيانات
        foreach ($results as $result) {
            $data = $this->matchDataToColumns($result, $columns);
            $col = 'A';
            
            foreach ($data as $value) {
                // معالجة القيم
                if (is_array($value)) {
                    $value = implode('|', $value);
                } elseif (is_object($value)) {
                    $value = method_exists($value, '__toString') ? $value->__toString() : '';
                }
                
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            
            // تنسيق الصف
            $lastCol = chr(ord('A') + count($data) - 1);
            $rowRange = 'A' . $row . ':' . $lastCol . $row;
            
            $sheet->getStyle($rowRange)->getBorders()
                  ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle($rowRange)->getAlignment()
                  ->setVertical(Alignment::VERTICAL_CENTER);
            
            // تلوين الصفوف الزوجية
            if (($row - 1) % 2 == 0) {
                $sheet->getStyle($rowRange)->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFF9F9F9');
            }
            
            $row++;
        }

        // ضبط عرض الأعمدة تلقائياً
        if ($options['autoSize']) {
            $lastCol = chr(ord('A') + count($columns) - 1);
            foreach (range('A', $lastCol) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // ضبط اتجاه النص للعربية
        if ($options['rtl']) {
            $sheet->setRightToLeft(true);
        }

        // إنشاء الملف
        $writer = new Xlsx($spreadsheet);
        $fileName = uniqid('oc');
        $filePath = temp_path() . '/' . $fileName . '.xlsx';
        
        $writer->save($filePath);
        
        // إعادة اسم الملف (بدون امتداد للتخزين)
        return $fileName . '.xlsx';
    }

    /**
     * Used to override column definitions at export time.
     */
    protected function exportExtendColumns($columns)
    {
        return $columns;
    }

    /**
     * Extracts the headers from the column definitions.
     */
    protected function getColumnHeaders($columns)
    {
        $headers = [];
        foreach ($columns as $column => $label) {
            $headers[] = Lang::get($label);
        }
        return $headers;
    }

    /**
     * Ensures the correct order of the column data.
     */
    protected function matchDataToColumns($data, $columns)
    {
        $results = [];
        foreach ($columns as $column => $label) {
            $value = array_get($data, $column);
            
            // معالجة القيم للمصفوفات والكائنات
            if (is_array($value)) {
                $value = $this->encodeArrayValue($value);
            } elseif (is_object($value)) {
                $value = method_exists($value, '__toString') ? $value->__toString() : '';
            }
            
            $results[] = $value;
        }
        return $results;
    }

    /**
     * Implodes a single dimension array using pipes (|)
     * Multi dimensional arrays are not allowed.
     * @return string
     */
    protected function encodeArrayValue($data, $delimeter = '|')
    {
        $newData = [];
        foreach ($data as $value) {
            if (is_array($value)) {
                $newData[] = 'Array';
            } else {
                $newData[] = str_replace($delimeter, '\\'.$delimeter, $value);
            }
        }
        return implode($delimeter, $newData);
    }
}