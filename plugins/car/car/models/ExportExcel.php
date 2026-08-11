<?php namespace Car\Car\Models;

class ExportExcel extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        // جلب جميع البيانات مع العلاقات
        $cars = Car::with(['country', 'brand', 'model'])->get();
        
        // إضافة BOM كبداية للملف
        $this->bom = "\xEF\xBB\xBF";
        
        foreach ($cars as $record) {
            $exportData = $record->toArray();
            
            // إضافة العلاقات مع معالجة الترميز
            $exportData['country_name'] = $this->formatText($record->country, 'name');
            $exportData['brand'] = $this->formatText($record->brand, 'name');
            $exportData['model'] = $this->formatText($record->model, 'name');
            $exportData['country_new'] = $this->formatText($record, 'country_new');
            
            // معالجة جميع الحقول النصية
            foreach ($exportData as $key => $value) {
                if (is_string($value)) {
                    $exportData[$key] = $this->fixEncoding($value);
                }
            }
            
            // جعل الأعمدة المحددة مرئية
            $record->addVisible($columns);
            
            $finalData = [];
            foreach ($columns as $column) {
                $finalData[$column] = isset($exportData[$column]) ? $this->fixEncoding($exportData[$column]) : '';
            }
            
            yield $finalData;
        }
    }
    
    /**
     * تنسيق النص من العلاقات
     */
    private function formatText($object, $field)
    {
        if (is_object($object) && property_exists($object, $field)) {
            return $this->fixEncoding($object->$field);
        } elseif (is_array($object) && isset($object[$field])) {
            return $this->fixEncoding($object[$field]);
        } elseif (is_string($object)) {
            return $this->fixEncoding($object);
        }
        return '';
    }
    
    /**
     * إصلاح مشاكل الترميز
     */
    private function fixEncoding($text)
    {
        if (is_null($text)) {
            return '';
        }
        
        $text = (string) $text;
        
        // تحويل إلى UTF-8
        $encoding = mb_detect_encoding($text, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
        if ($encoding && $encoding != 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        }
        
        // تنظيف النص
        $text = iconv('UTF-8', 'UTF-8//IGNORE', $text);
        $text = htmlspecialchars_decode($text, ENT_QUOTES);
        $text = strip_tags($text);
        $text = trim($text);
        
        return $text;
    }
    
    /**
     * تخصيص طريقة التصدير لإضافة BOM
     */
    public function getExportData($columns, $sessionKey = null)
    {
        $data = parent::getExportData($columns, $sessionKey);
        
        // إضافة BOM إذا كان الملف CSV
        if (isset($this->bom)) {
            return $this->bom . $data;
        }
        
        return $data;
    }
}