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
            
            // إضافة العلاقات مع التحقق من القيم
            $exportData['country_name'] = $this->getSafeValue($record->country, 'name');
            $exportData['brand'] = $this->getSafeValue($record->brand, 'name');
            $exportData['model'] = $this->getSafeValue($record->model, 'name');
            $exportData['country_new'] = $this->getSafeString($record->country_new ?? '');
            
            // تصفية الأعمدة مع تحويل آمن
            $finalData = [];
            foreach ($columns as $column) {
                $value = isset($exportData[$column]) ? $exportData[$column] : '';
                $finalData[$column] = $this->convertToString($value);
            }
            
            yield $finalData;
        }
    }
    
    /**
     * الحصول على قيمة آمنة من العلاقة
     */
    private function getSafeValue($object, $field)
    {
        if (is_object($object) && method_exists($object, $field)) {
            return $object->$field() ?? '';
        } elseif (is_object($object) && property_exists($object, $field)) {
            return $object->$field ?? '';
        } elseif (is_array($object) && isset($object[$field])) {
            return $object[$field];
        }
        return '';
    }
    
    /**
     * الحصول على نص آمن
     */
    private function getSafeString($value)
    {
        if (is_null($value)) {
            return '';
        }
        
        if (is_array($value)) {
            return implode(', ', $value);
        }
        
        if (is_object($value)) {
            return method_exists($value, '__toString') ? $value->__toString() : '';
        }
        
        return (string) $value;
    }
    
    /**
     * تحويل أي قيمة إلى نص بشكل آمن
     */
    private function convertToString($value)
    {
        // التعامل مع القيم الفارغة
        if (is_null($value)) {
            return '';
        }
        
        // التعامل مع المصفوفات
        if (is_array($value)) {
            return implode(', ', array_map([$this, 'convertToString'], $value));
        }
        
        // التعامل مع الكائنات
        if (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $value->__toString();
            }
            
            // محاولة الحصول على الاسم إذا كان Eloquent model
            if (method_exists($value, 'getNameAttribute')) {
                return $value->getNameAttribute() ?? '';
            }
            
            // محاولة الحصول على name
            if (isset($value->name)) {
                return (string) $value->name;
            }
            
            // محاولة الحصول على title
            if (isset($value->title)) {
                return (string) $value->title;
            }
            
            // إذا كان كائن stdClass
            if ($value instanceof \stdClass) {
                $array = (array) $value;
                return implode(', ', $array);
            }
            
            return '';
        }
        
        // تحويل إلى نص
        return (string) $value;
    }
}