<?php namespace Car\Car\Models;

class ExportExcel extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        // جلب جميع البيانات مع العلاقات
        $cars = Car::with(['country', 'brand', 'model'])->get();
        
        foreach ($cars as $record) {
            // إضافة اسم المدينة إلى البيانات
            $exportData = $record->toArray();
            
            // إضافة العلاقات مع تحسين الترميز
            $exportData['country_name'] = $record->country ? $this->cleanText($record->country->name) : '';
            $exportData['brand'] = $record->brand ? $this->cleanText($record->brand->name) : '';
            $exportData['model'] = $record->model ? $this->cleanText($record->model->name) : '';
            $exportData['country_new'] = $record->country_new ? $this->cleanText($record->country_new) : '';
            
            // جعل الأعمدة المحددة مرئية
            $record->addVisible($columns);
            
            // دمج البيانات مع الأعمدة المرئية
            $finalData = [];
            foreach ($columns as $column) {
                if (isset($exportData[$column])) {
                    $finalData[$column] = $this->cleanText($exportData[$column]);
                } else {
                    $finalData[$column] = '';
                }
            }
            
            yield $finalData;
        }
    }
    
    /**
     * تنظيف النص وتحسين الترميز
     */
    private function cleanText($text)
    {
        if (is_null($text)) {
            return '';
        }
        
        // تحويل إلى UTF-8 إذا لم يكن
        if (!mb_detect_encoding($text, 'UTF-8', true)) {
            $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        }
        
        // إزالة الأحرف غير المرغوب فيها
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        
        return $text;
    }
}