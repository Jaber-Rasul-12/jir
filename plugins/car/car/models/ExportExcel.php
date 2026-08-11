<?php namespace Car\Car\Models;

class ExportExcel extends \Backend\Models\ExportModel
{
    private $bom = "\xEF\xBB\xBF";
    
    public function exportData($columns, $sessionKey = null)
    {
        // جلب البيانات مع العلاقات
        $cars = Car::with(['country', 'brand', 'model'])->get();
        
        foreach ($cars as $record) {
            $exportData = $record->toArray();
            
            // إضافة العلاقات
            $exportData['country_name'] = $record->country ? $record->country->name : '';
            $exportData['brand'] = $record->brand ? $record->brand->name : '';
            $exportData['model'] = $record->model ? $record->model->name : '';
            $exportData['country_new'] = $record->country_new ?? '';
            
            // تصفية الأعمدة مع تنظيف النصوص
            $finalData = [];
            foreach ($columns as $column) {
                $value = isset($exportData[$column]) ? $exportData[$column] : '';
                $finalData[$column] = $this->fixExcelText($value);
            }
            
            yield $finalData;
        }
    }
    
    /**
     * معالجة النصوص لـ Excel 2019 العربي
     */
    private function fixExcelText($text)
    {
        if (is_null($text)) {
            return '';
        }
        
        $text = (string) $text;
        
        // 1. تحويل إلى UTF-8 مع BOM
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        // 2. تنظيف HTML
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // 3. إزالة الأحرف غير المرغوب فيها
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        
        // 4. معالجة خاصة للعربية
        $text = $this->arabicFix($text);
        
        return trim($text);
    }
    
    /**
     * معالجة خاصة للنصوص العربية
     */
    private function arabicFix($text)
    {
        // إصلاح تشكيل الحروف العربية
        $arabicChars = [
            'á' => 'ا', 'í' => 'ي', 'ó' => 'و',
            'ä' => 'ة', 'ï' => 'ي', 'ö' => 'و',
            'â' => 'ا', 'î' => 'ي', 'ô' => 'و',
            'Á' => 'ا', 'Í' => 'ي', 'Ó' => 'و',
            'Ä' => 'ة', 'Ï' => 'ي', 'Ö' => 'و',
            'Â' => 'ا', 'Î' => 'ي', 'Ô' => 'و'
        ];
        
        $text = str_replace(array_keys($arabicChars), array_values($arabicChars), $text);
        
        return $text;
    }
}