<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<h1>Таблица запросов</h1>
<form id="filterForm">
    <label for="dateFrom">Дата от:</label>
    <input type="date" id="dateFrom" name="dateFrom">
    <label for="dateTo">Дата до:</label>
    <input type="date" id="dateTo" name="dateTo">
    <label for="os">ОС:</label>
    <select id="os" name="os">
        <option value="">Все</option>
        <option value="Windows">Windows</option>
        <option value="Android">Android</option>
        <option value="Linux">Linux</option>
    </select>
    <label for="architecture">Архитектура:</label>
    <select id="architecture" name="architecture">
        <option value="">Все</option>
        <option value="x86">x86</option>
        <option value="x64">x64</option>
    </select>
    <button type="submit">Применить фильтр</button>
</form>
<table class="table">
    <tr>
        <th>Дата запроса</th>
        <th>Число запросов</th>
        <th>Самый популярный URL</th>
        <th>Самый популярный браузер</th>

    </tr>
    <?php use yii\helpers\Html;
    foreach ($data as $row): ?>
        <tr>
            <td><?= Html::encode($row['date']) ?></td>
            <td><?= Html::encode($row['request_count']) ?></td>
            <td><?= Html::encode($row['popular_url']) ?></td>
            <td><?= Html::encode($row['popular_browser']) ?></td>

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
    $popularBrowsers[] = $row['popular_browser'];
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
        series: [
            " . (isset($popularBrowsers[0]) ? "
            {
                name: " . json_encode($popularBrowsers[0]) . ",
                data: " . json_encode($browserSharesPercentage) . "
            }," : "") . (isset($popularBrowsers[1]) ? "
            {
                name: " . json_encode($popularBrowsers[1]) . ",
                data: " . json_encode($browserSharesPercentage) . "
            }," : "") . (isset($popularBrowsers[2]) ? "
            {
                name: " . json_encode($popularBrowsers[2]) . ",
                data: " . json_encode($browserSharesPercentage) . "
            }" : "") . "
        ]
    });
");





$this->registerJs("
   
    $('#filterForm').submit(function(event) {
        event.preventDefault(); // Prevent form submission

        var dateFrom = $('#dateFrom').val();
        var dateTo = $('#dateTo').val();
        var os = $('#os').val();
        var architecture = $('#architecture').val();

        var queryParams = '?dateFrom=' + dateFrom + '&dateTo=' + dateTo + '&os=' + os + '&architecture=' + architecture;
        window.location.href = window.location.pathname + queryParams;
    });
");
?>
<style>
    th:hover {
        cursor: pointer; /* Изменение формы курсора при наведении на заголовки таблицы */
    }
</style>
<script>
    // Добавление возможности сортировки для каждой колонки таблицы
    $(document).ready(function(){
        $('table').each(function(){
            $(this).find('th').slice(0, 4).click(function(){
                var table = $(this).parents('table').eq(0);
                var rows = table.find('tr:gt(0)').toArray().sort(comparer($(this).index()));
                this.asc = !this.asc;
                if (!this.asc){rows = rows.reverse();}
                for (var i = 0; i < rows.length; i++){table.append(rows[i]);}
            });
        });
        function comparer(index) {
            return function(a, b) {
                var valA = getCellValue(a, index), valB = getCellValue(b, index);
                return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
            };
        }
        function getCellValue(row, index){ return $(row).children('td').eq(index).text(); }
    });


    $('#filterForm').submit(function(event) {
        event.preventDefault();


        var dateFrom = $('#dateFrom').val();
        var dateTo = $('#dateTo').val();
        var os = $('#os').val();
        var architecture = $('#architecture').val();


        var queryParams = '?dateFrom=' + dateFrom + '&dateTo=' + dateTo + '&os=' + os + '&architecture=' + architecture;


        window.location.href = window.location.pathname + queryParams;
    });


    $(document).ready(function() {
        var urlParams = new URLSearchParams(window.location.search);
        $('#dateFrom').val(urlParams.get('dateFrom'));
        $('#dateTo').val(urlParams.get('dateTo'));
        $('#os').val(urlParams.get('os'));
        $('#architecture').val(urlParams.get('architecture'));
    });


</script>