<?php namespace Car\Car\Models;

class ExportExcel extends \Backend\Models\ExportModel
{
    private $bom = "\xEF\xBB\xBF";
    
    public function exportData($columns, $sessionKey = null)
    {
        // جلب البيانات مع العلاقات
        $cars = Car::with(['country', 'brand', 'model'])->cursor();
        
        foreach ($cars as $record) {
            $exportData = $record->toArray();
            
            // إضافة العلاقات مع تحويل آمن
            $exportData['country_name'] = $this->convertToUtf8($record->country ? $record->country->name : '');
            $exportData['brand'] = $this->convertToUtf8($record->brand ? $record->brand->name : '');
            $exportData['model'] = $this->convertToUtf8($record->model ? $record->model->name : '');
            $exportData['country_new'] = $this->convertToUtf8($record->country_new ?? '');
            
            // تصفية الأعمدة
            $finalData = [];
            foreach ($columns as $column) {
                $finalData[$column] = isset($exportData[$column]) ? $this->convertToUtf8($exportData[$column]) : '';
            }
            
            yield $finalData;
        }
    }
    
    /**
     * تحويل آمن إلى UTF-8
     */
    private function convertToUtf8($text)
    {
        if (is_null($text)) {
            return '';
        }
        
        $text = (string) $text;
        
        // استخدام iconv أولاً
        $text = @iconv('UTF-8', 'UTF-8//IGNORE', $text);
        
        // إذا كان النص لا يزال مشوهاً، حاول التحويل القسري
        if (mb_check_encoding($text, 'UTF-8') === false) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // تنظيف
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        $text = htmlspecialchars_decode($text, ENT_QUOTES | ENT_SUBSTITUTE);
        $text = strip_tags($text);
        $text = trim($text);
        
        return $text;
    }
}