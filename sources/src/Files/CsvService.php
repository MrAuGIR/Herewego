<?php

namespace App\Files;

use DateTime;


class CsvService 
{
    /**
     * Créé un fichier csv et retourne le chemin
     */
    public static function createEventCsv($datas) : string
    {
        $events = [];
        foreach ($datas as $event) {
            $row = [
                $event->getTitle(),
                $event->getStartedAt()->format('Y-m-d'),
                $event->getEndedAt()->format('Y-m-d'),
                $event->getCountViews(),
                $event->getCategory()->getName(),
                $event->getLocalisation()->getCityName(),
                $event->getUser()->getLastName()
            ];
            $events[] = $row;
        }
        $date = new DateTime();
        $timestamp = $date->getTimestamp();
        $fileName = "file-".$timestamp.".csv";
        $file = fopen('csv/'.$fileName, 'w');         
        foreach ($events as $event) {
            fputcsv($file, $event);
        }         
        fclose($file);
        return $fileName;
    }
}