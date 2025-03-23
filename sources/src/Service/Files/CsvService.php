<?php

namespace App\Service\Files;

use App\Entity\Event;
use App\Service\Files\Exception\CsvException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

class CsvService
{
    public function __construct(
        #[Autowire(param: 'csv_directory')] private string $csvDirectory
    )
    {
    }

    /**
     * @param Event[] $events
     * @throws CsvException
     */
    public function createEventCsv(array $events): string
    {
        $dataEvents = [];
        foreach($this->generateLine($events) as $line) {
            $dataEvents[] = $line;
        }
        return  $this->createFile($dataEvents);
    }

    private function generateLine(array $events): \Generator
    {
        foreach ($events as $event) {
            yield [
                $event->getTitle(),
                $event->getStartedAt()->format('Y-m-d'),
                $event->getEndedAt()->format('Y-m-d'),
                $event->getCountViews(),
                $event->getCategory()->getName(),
                $event->getLocalisation()->getCityName(),
                $event->getUser()->getLastName(),
            ];
        }
    }

    /**
     * @throws CsvException
     */
    private function createFile(array $dataEvents): string
    {
        $fileSystem = new Filesystem();
        $fileName = $this->createFileName();
        $filePath = $this->csvDirectory . DIRECTORY_SEPARATOR . $fileName;

        try {
            $fileSystem->mkdir($this->csvDirectory);

            $file = new \SplFileObject($filePath, 'w');

            foreach ($dataEvents as $event) {
                $file->fputcsv($event);
            }

        } catch (\Exception $exception) {
            throw new CsvException("Erreur lors de la création du fichier : " . $exception->getMessage());
        }

        return $fileName;
    }

    private function createFileName(): string
    {
        $date = new \DateTime();
        $timestamp = $date->getTimestamp();
        return 'file-'.$timestamp.'.csv';
    }
}
