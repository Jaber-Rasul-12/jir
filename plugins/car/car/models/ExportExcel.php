<?php namespace Car\Car\Models;

class ExportExcel extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        // جلب جميع البيانات مع العلاقات
        $cars = Car::with('country')->get();
        
        foreach ($cars as $record) {
            // إضافة اسم المدينة إلى البيانات
            $exportData = $record->toArray();
            
            // إضافة اسم المدينة إذا كانت العلاقة موجودة
            if ($record->country) {
                $exportData['country_name'] = $record->country->name;
            } else {
                $exportData['country_name'] = '';
            }
            
            // إضافة الحقول الإضافية للتصدير
            $exportData['city_name'] = $record->country ? $record->country->name : '';
            
            // جعل الأعمدة المحددة مرئية
            $record->addVisible($columns);
            
            // دمج البيانات مع الأعمدة المرئية
            $finalData = [];
            foreach ($columns as $column) {
                if ($column == 'country_name' || $column == 'city_name') {
                    $finalData[$column] = $exportData[$column] ?? '';
                } elseif (isset($exportData[$column])) {
                    $finalData[$column] = $exportData[$column];
                }
            }
            
            yield $finalData;
        }
    }
}