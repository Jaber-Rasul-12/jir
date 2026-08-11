<?php namespace Car\Car\Models;

class ExportExcel extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        // جلب البيانات مع العلاقات
        $cars = Car::with(['country', 'brand', 'model'])->get();
        
        foreach ($cars as $record) {
            $exportData = $record->toArray();
            
            // إضافة العلاقات مع تحويل الترميز
            $exportData['country_name'] = $this->safeEncode($record->country ? $record->country->name : '');
            $exportData['brand'] = $this->safeEncode($record->brand ? $record->brand->name : '');
            $exportData['model'] = $this->safeEncode($record->model ? $record->model->name : '');
            $exportData['country_new'] = $this->safeEncode($record->country_new ?? '');
            
            // تصفية الأعمدة
            $finalData = [];
            foreach ($columns as $column) {
                $finalData[$column] = isset($exportData[$column]) ? $this->safeEncode($exportData[$column]) : '';
            }
            
            yield $finalData;
        }
    }
    
    /**
     * معالجة الترميز بشكل جذري
     */
    private function safeEncode($text)
    {
        if (is_null($text)) {
            return '';
        }
        
        $text = (string) $text;
        
        // 1. تحويل إلى UTF-8
        $encoding = mb_detect_encoding($text, ['UTF-8', 'Windows-1256', 'ISO-8859-6', 'ASCII'], true);
        
        if ($encoding && $encoding !== 'UTF-8') {
            $text = mb_convert_encoding($text, 'UTF-8', $encoding);
        } elseif (!$encoding) {
            // إذا لم يتم التعرف على الترميز، حاول التحويل القسري
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        // 2. إزالة الأحرف غير الصالحة
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
        
        // 3. تنظيف HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = strip_tags($text);
        
        // 4. إزالة المسافات الزائدة
        $text = trim($text);
        
        return $text;
    }
}