<script src="https://code.highcharts.com/highcharts.js"></script>
<h1>Таблица запросов</h1>
<table class="table">
    <tr>
        <th>Дата запроса</th>
        <th>Число запросов</th>
        <th>Самый популярный URL</th>
        <th>Самый популярный браузер</th>
        <th>Доля запросов</th>
    </tr>
    <?php use yii\helpers\Html;
    foreach ($data as $row): ?>
        <tr>
            <td><?= Html::encode($row['date']) ?></td>
            <td><?= Html::encode($row['request_count']) ?></td>
            <td><?= Html::encode($row['popular_url']) ?></td>
            <td><?= Html::encode($row['popular_browser']) ?></td>
            <td><?= Html::encode($row['browser_share']) ?></td>
        </tr>
    <?php endforeach; ?>
</table>

<div id="chart1"></div>
<div id="chart2"></div>

<?php
// График 1: по оси x - даты, по оси y - число запросов
$dates = [];
$requestCounts = [];
foreach ($data as $row) {
    $dates[] = $row['date'];
    $requestCounts[] = $row['request_count'];
}

$this->registerJs("
    Highcharts.chart('chart1', {
        title: {
            text: 'График 1: Число запросов по датам'
        },
        xAxis: {
            categories: " . json_encode($dates) . "
        },
        yAxis: {
            title: {
                text: 'Число запросов'
            }
        },
        series: [{
            name: 'Число запросов',
            data: " . json_encode($requestCounts) . "
        }]
    });
");

// График 2: по оси x - даты, по оси y - доля (% от числа запросов) для трех самых популярных браузеров
$browserSharesPercentage = [];
$popularBrowsers = [];
foreach ($data as $row) {
    $browserSharesPercentage[] = $row['browser_share'];
    $popularBrowsers[] = $row['popular_browser'];// Assuming 'browser_share' already represents the percentage
}

$this->registerJs("
    Highcharts.chart('chart2', {
        title: {
            text: 'График 2: Доля запросов для 3 самых популярных браузеров'
        },
        xAxis: {
            categories: " . json_encode($dates) . "
        },
        yAxis: {
            title: {
                text: 'Доля запросов (%)'
            }
        },
        series: [{
            name: " . json_encode($popularBrowsers[0]) . ",
            data: " . json_encode($browserSharesPercentage) . "
        },
        {
            name: " . json_encode($popularBrowsers[1]) . ",
            data: " . json_encode($browserSharesPercentage) . "
        },
        {
            name: " . json_encode($popularBrowsers[2]) . ",
            data: " . json_encode($browserSharesPercentage) . "
        }
        ]
    });
");


?>
