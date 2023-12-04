<?php

namespace app\commands;

use yii\console\Controller;
use yii\console\ExitCode;
use app\models\LogData; // Предположим, что у вас есть модель LogData для работы с данными логов

class ParseLogController extends Controller
{
    public function actionIndex()
    {
        $filePath = 'web/logs.1'; // Путь к файлу логов

        if (!file_exists($filePath)) {
            echo "Файл логов не найден.\n";
            return ExitCode::UNSPECIFIED_ERROR;
        }

        $fileContent = file_get_contents($filePath); // Получаем содержимое файла

        $lines = explode("\n", $fileContent); // Разбиваем содержимое файла на строки

        foreach ($lines as $line) {
            $data = $this->parseLine($line); // Парсим каждую строку

            // Создаем новый объект модели LogData
            $logData = new LogData();
            $logData->ip = $data['ip'];
            $logData->timedate = $data['timedate'];
            $logData->url = $data['url'];
            $logData->os = $data['os'];
            $logData->architecture = $data['architecture'];
            $logData->browser = $data['browser'];


            if (!$logData->save()) {
                echo "Произошла ошибка при сохранении данных в базу данных.\n";
                return ExitCode::UNSPECIFIED_ERROR;
            }
        }

        echo "Данные успешно загружены в базу данных.\n";
        return ExitCode::OK;
    }

    private function parseLine($line)
    {
        $pattern = '/^(\S+) \S+ \S+ \[(.*?)\] "(.*?)" \S+ \S+ "(.*?)" "(.*?)"/';
        preg_match($pattern, $line, $matches);

        $ip = $matches[1];
        $timedate = $this->parseTimedate($matches[2]); // Извлекаем дату с помощью нового метода parseTimedate
        $url = $this->parseUrl($matches[3]);
        $userAgent = $matches[5];
        $os = $this->parseOs($userAgent);
        $architecture = $this->parseArchitecture($userAgent);
        $browser = $this->parseBrowser($userAgent);

        return [
            'ip' => $ip,
            'timedate' => $timedate,
            'url' => $url,
            'os' => $os,
            'architecture' => $architecture,
            'browser' => $browser,
        ];
    }

    private function parseTimedate($timedate)
    {
        $dateTime = \DateTime::createFromFormat('d/M/Y:H:i:s O', $timedate);
        return $dateTime->format('Y-m-d H:i:s');
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
        } elseif (strpos($userAgent, 'Firefox') !== false) {
            $browser = 'Firefox';
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

}
