<?php

namespace app\commands;

use Yii;
use yii\console\Controller;
use yii\console\ExitCode;
use app\models\LogData; // Предположим, что у вас есть модель LogData для работы с данными логов

class ParseLogController extends Controller
{
    public function actionIndex()
    {
        $filePath = 'web/logs.1';

        if (!file_exists($filePath)) {
            echo "Файл логов не найден.\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $batchSize = 1000;
        $lines = file($filePath, FILE_IGNORE_NEW_LINES); // Считываем файл построчно


        $batchData = [];
        foreach ($lines as $line) {
            $data = $this->parseLine($line);
            $batchData[] = $data;

            if (count($batchData) === $batchSize) {
                $this->saveBatchData($batchData);
                $batchData = []; // Очищаем массив для следующего пакета данных
            }
        }

        // Сохраняем оставшиеся данные, если их количество меньше $batchSize
        if (!empty($batchData)) {
            $this->saveBatchData($batchData);
            $this->deleteInvalidDates();
        }

        echo "Данные успешно загружены в базу данных.\n";
        return ExitCode::OK;
    }

    private function parseLine($line)
    {
        $pattern = '/^(\S+) \S+ \S+ \[(.*?)\] "(.*?)" \S+ \S+ "(.*?)" "(.*?)"/';
        preg_match($pattern, $line, $matches);

        $ip = isset($matches[1]) ? $this->parseIp($matches[1]) : null;
        $timedate = isset($matches[2]) ? $this->parseTimedate($matches[2]) : null;
        $url = isset($matches[3]) ? $this->parseUrl($matches[3]) : null;
        $userAgent = isset($matches[5]) ? $matches[5] : null;
        $os = $userAgent ? $this->parseOs($userAgent) : null;
        $architecture = $userAgent ? $this->parseArchitecture($userAgent) : null;
        $browser = $userAgent ? $this->parseBrowser($userAgent) : null;


        return [
            'ip' => $ip,
            'timedate' => $timedate,
            'url' => $url,
            'os' => $os,
            'architecture' => $architecture,
            'browser' => $browser,
        ];
    }

    private function parseIp($ip)
    {
        $pattern = '/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})$/';
        preg_match($pattern, $ip, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        } else {
            // Обработка случая, когда совпадение не найдено или нет захватывающих групп
            return "Unknow"; // или любое другое значение по вашему выбору
        }
    }

    private function parseTimedate($timedate)
    {
        $formats = [
            'd/M/Y:H:i:s O',
            'd/M/Y:H:i:s P',
            'd/M/Y:H:i:s e',
            'd/M/Y:H:i:s T',
            'd/M/Y:H:i:s Z',
            'd/M/Y:H:i:s',
        ];

        foreach ($formats as $format) {
            $dateTime = \DateTimeImmutable::createFromFormat($format, $timedate);
            if ($dateTime !== false) {
                return $dateTime->format('Y-m-d H:i:s');
            }
        }

        // Если дата не была распознана ни в одном из форматов, вернуть null или другое значение по вашему выбору
        return null;
    }






    private function parseUrl($request)
    {
        $pattern = '/^GET (.*?) HTTP/';
        preg_match($pattern, $request, $matches);

        if (isset($matches[1])) {
            return $matches[1];
        } else {
            // Обработка случая, когда совпадение не найдено или нет захватывающих групп
            return null; // Или любое другое значение по вашему выбору
        }
    }


    private function parseOs($userAgent)
    {
        $os = 'Unknown';

        if (strpos($userAgent, 'Windows') !== false) {
            $os = 'Windows';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $os = 'Linux';
        } elseif (strpos($userAgent, 'Macintosh') !== false) {
            $os = 'Macintosh';
        } elseif (strpos($userAgent, 'Android') !== false) {
            $os = 'Android';
        } elseif (strpos($userAgent, 'iOS') !== false) {
            $os = 'iOS';
        }

        return $os;
    }


    private function parseArchitecture($userAgent)
    {
        $architecture = 'Unknown';

        if (strpos($userAgent, 'x86') !== false) {
            $architecture = 'x86';
        } elseif (strpos($userAgent, 'x64') !== false) {
            $architecture = 'x64';
        }

        return $architecture;
    }


    private function parseBrowser($userAgent)
    {
        $browser = 'Unknown';

        if (strpos($userAgent, 'Chrome') !== false) {
            $browser = 'Chrome';
        } elseif (strpos($userAgent, 'Mozilla') !== false) {
            $browser = 'Mozilla';
        } elseif (strpos($userAgent, 'Safari') !== false) {
            $browser = 'Safari';
        } elseif (strpos($userAgent, 'Opera') !== false || strpos($userAgent, 'OPR') !== false) {
            $browser = 'Opera';
        } elseif (strpos($userAgent, 'Edge') !== false) {
            $browser = 'Edge';
        } elseif (strpos($userAgent, 'IE') !== false || strpos($userAgent, 'Trident') !== false) {
            $browser = 'Internet Explorer';
        }

        return $browser;
    }

    private function saveBatchData($batchData)
    {
        $result = LogData::getDb()->createCommand()->batchInsert(LogData::tableName(), [
            'ip', 'timedate', 'url', 'os', 'architecture', 'browser'
        ], $batchData)->execute();

        if (!$result) {
            echo "Произошла ошибка при сохранении данных в базу данных.\n";
            exit(1); // Используем exit() вместо return для завершения выполнения скрипта с ошибкой
        }
    }

    private function deleteInvalidDates()
    {
        $connection = Yii::$app->getDb();
        $command = $connection->createCommand("
        DELETE FROM logs WHERE timedate = '0000-00-00 00:00:00'
    ");
        $result = $command->execute();

        if ($result === false) {
            echo "Произошла ошибка при удалении записей с недопустимой датой из базы данных.\n";
            exit(1); // Используем exit() вместо return для завершения выполнения скрипта с ошибкой
        }
    }



}
