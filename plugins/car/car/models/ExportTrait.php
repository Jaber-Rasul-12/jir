<?php namespace Car\Car\Models;

trait ExportTrait
{
    public function getExportFileName($name = null)
    {
        // إضافة BOM للملف
        $filename = parent::getExportFileName($name);
        
        // إضافة BOM للـ CSV
        if (strpos($filename, '.csv') !== false) {
            $bom = "\xEF\xBB\xBF";
            $this->bom = $bom;
        }
        
        return $filename;
    }
}