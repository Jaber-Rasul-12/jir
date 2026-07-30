<?php namespace Car\Car\Models;

class ExportExcel extends \Backend\Models\ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        foreach (Car::cursor() as $record) {
            $record->addVisible($columns);
            yield $record->toArray();
        }
    }
} 