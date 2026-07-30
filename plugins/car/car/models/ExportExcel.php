<?php namespace Car\Car\Models;

use Backend\Models\ExportModel;

class ExportExcel extends ExportModel
{
    /**
     * @var array العلاقات التي سيتم تحميلها مسبقاً
     */
    public $with = ['brand', 'model', 'country', 'country_new'];

    /**
     * تصدير البيانات
     */
    public function exportData($columns, $sessionKey = null)
    {
        $cars = Car::with($this->with)->cursor();

        foreach ($cars as $record) {
            $data = [
                'id' => $record->id,
                'ownership' => $record->ownership ?? '',
                'type' => $record->type ?? '',
                'chassis_number' => $record->chassis_number ?? '',
                'year_of_manufacturing_date' => $this->formatDate($record->year_of_manufacturing_date),
                'fuel_type' => $record->fuel_type ?? '',
                'fuel_type_label' => $this->getFuelLabel($record->fuel_type),
                'license_plate_number' => $record->license_plate_number ?? '',
                'license_plate_number_new' => $record->license_plate_number_new ?? '',
                'brand_name' => $record->brand->name ?? '',
                'model_name' => $record->model->name ?? '',
                'country_name' => $record->country->name ?? '',
                'country_new_name' => $record->country_new->name ?? '',
                'created_at' => $this->formatDateTime($record->created_at),
                'updated_at' => $this->formatDateTime($record->updated_at),
            ];

            // إظهار الأعمدة المطلوبة فقط
            $filteredData = array_intersect_key($data, array_flip($columns));
            
            yield $filteredData;
        }
    }

    /**
     * الحصول على تسمية نوع الوقود
     */
    private function getFuelLabel($fuelType)
    {
        $fuels = [
            'petrol' => trans('car.car::lang.model.car.petrol'),
            'diesel' => trans('car.car::lang.model.car.diesel'),
            'electric' => trans('car.car::lang.model.car.electric'),
            'hybrid' => trans('car.car::lang.model.car.hybrid'),
            'plug-in_hybrid' => trans('car.car::lang.model.car.plug_in_hybrid'),
            'hydrogen' => trans('car.car::lang.model.car.hydrogen'),
            'lpg' => trans('car.car::lang.model.car.lpg'),
            'cng' => trans('car.car::lang.model.car.cng'),
        ];

        return $fuels[$fuelType] ?? $fuelType ?? '';
    }

    /**
     * تنسيق التاريخ
     */
    private function formatDate($date)
    {
        if ($date) {
            return date('Y-m-d', strtotime($date));
        }
        return '';
    }

    /**
     * تنسيق التاريخ والوقت
     */
    private function formatDateTime($date)
    {
        if ($date) {
            return date('Y-m-d H:i:s', strtotime($date));
        }
        return '';
    }
}