// Set onclick for minus signs in archives
$('.list-minimizer').click(function() {
    if ($(this).hasClass('glyphicon-minus-sign')) {
        $(this).next('ul').hide();
        $(this).removeClass('glyphicon-minus-sign');
        $(this).addClass('glyphicon-plus-sign');
    } else {
        $(this).next('ul').show();
        $(this).removeClass('glyphicon-plus-sign');
        $(this).addClass('glyphicon-minus-sign');
    }
});

function graphDates(search) {
    $.getJSON("./REST/dates.php", "search=" + search, function(data) {
        var dataArray = [['Abbreviation', 'Count']];

        for (var i = 0; i < data.length; ++i) {
            dataArray.push([data[i].abbreviation, parseInt(data[i].count)]);
        }

        var chartData = google.visualization.arrayToDataTable(dataArray);
        var options = {
            'title': 'Date Distribution',
            'width': 1000,
            'height': 700
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('date-distribution'));
        chart.draw(chartData, options);
    });
}

function graphNames(search) {
    $.getJSON("./REST/names.php", "search=" + search, function(data) {
        var dataArray = [['Name', 'Count']];

        for (var i = 0; i < Math.min(data.length, 20); ++i) {
            dataArray.push([data[i].name_text, parseInt(data[i].count)]);
        }

        var chartData = google.visualization.arrayToDataTable(dataArray);
        var options = {
            'title': 'Name Distribution',
            'width': 1000,
            'height': 700,
            'hAxis': {slantedText: true, slantedTextAngle: 80}
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('name-distribution'));
        chart.draw(chartData, options);
    });
}

function graphAttestation(search) {
    $.getJSON("./REST/names_dates.php", "search=" + search, function(data) {
        var chartData = google.visualization.arrayToDataTable(data);
        var options = {
            title: 'Names vs Dates (' + search + ')',
            width: 1000,
            height: 700,
            lineWidth: 0,
            pointSize: 7,
            hAxis: {title: 'Date'},
            vAxis: {title: 'Name Occurances'},
            legend: 'right'
        };

        var chart = new google.visualization.LineChart(document.getElementById('attestation-graph'));
        chart.draw(chartData, options);
    });
}

function logout()
{
    $('#logout')[0].submit();
}
