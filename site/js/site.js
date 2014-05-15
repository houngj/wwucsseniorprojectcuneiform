// Set onclick for minus signs in archives
$('.list-minimizer').click(function() {
    if ($(this).hasClass('glyphicon-minus-sign')) {
        $(this).siblings('ul').hide();
        $(this).removeClass('glyphicon-minus-sign');
        $(this).addClass('glyphicon-plus-sign');
    } else {
        $(this).siblings('ul').show();
        $(this).removeClass('glyphicon-plus-sign');
        $(this).addClass('glyphicon-minus-sign');
    }
});

function addTabletToNewArchive(tabletID) {
    var archiveName = prompt("Name of new archive:", "New Virtual Archive");
    if (archiveName !== null) {
        var d = 'action=new_archive&title=' + encodeURIComponent(archiveName);
        $.ajax({
            dataType: 'json',
            url: 'REST/archive.php',
            data: d,
            success: function(data) {
                addTabletToArchive(data, tabletID);
            },
            error: function(data) {
                alert('An error has occurred:\n' +
                      'URL:    ' + 'REST/archive.php\n' +
                      'DATA:   ' + d + '\n' +
                      'FUNCT:  ' + 'addTabletToNewArchive\n' +
                      'STATUS: ' + data.statusText + "\n" +
                      'ERROR:  ' + data.responseJSON);
            }
        });
    }
}

function addTabletToArchive(archiveID, tabletID) {
    var d = 'action=add_tablet&archive_id=' + encodeURIComponent(archiveID) + '&tablet_group_id=' + encodeURIComponent(tabletID);
    $.ajax({
        dataType: 'json',
        url: 'REST/archive.php',
        data: d,
        success: function(data) {
            // TODO: Instead of reloading the page, fetch the archives via REST.
            location.reload();
        },
        error: function(data) {
            alert('An error has occurred:\n' +
                  'URL:    ' + 'REST/archive.php\n' +
                  'DATA:   ' + d + '\n' +
                  'FUNCT:  ' + 'addTabletToArchive\n' +
                  'STATUS: ' + data.statusText + "\n" +
                  'ERROR:  ' + data.responseJSON);
        }
    });
}


function graphDates(search) {
    var d = 'search=' + search;
    $.ajax({
        dataType: 'json',
        url: 'REST/dates.php',
        data: d,
        success: function(data) {
            var dataArray = [['Abbreviation', 'Count']];
            for (var i = 0; i < data.length; ++i) {
                dataArray.push([data[i].abbreviation, parseInt(data[i].count)]);
            }

            var chartData = google.visualization.arrayToDataTable(dataArray);
            var options = {
                'title': 'Date Distribution',
                'width': 1000,
                'height': 700,
                'hAxis': {slantedText: true, slantedTextAngle: 80}
            };

            var chart = new google.visualization.ColumnChart(document.getElementById('date-distribution'));
            chart.draw(chartData, options);
        },
        error: function(data) {
            alert('An error has occurred:\n' +
                  'URL:    ' + 'REST/dates.php\n' +
                  'DATA:   ' + d + '\n' +
                  'FUNCT:  ' + 'graphDates\n' +
                  'STATUS: ' + data.statusText + "\n" +
                  'ERROR:  ' + data.responseJSON);
        }
    });
}

function graphNames(search) {
    var d = 'search=' + search;
    $.ajax({
        dataType: 'json',
        url: 'REST/names.php',
        data: d,
        success: function(data) {
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
        },
        error: function(data) {
            alert('An error has occurred:\n' +
                  'URL:    ' + 'REST/names.php\n' +
                  'DATA:   ' + d + '\n' +
                  'FUNCT:  ' + 'graphDates\n' +
                  'STATUS: ' + data.statusText + "\n" +
                  'ERROR:  ' + data.responseJSON);
        }
    });
}

function graphAttestation(search) {
    var d = 'search=' + search;
    $.ajax({
        dataType: 'json',
        url: 'REST/names_dates.php',
        data: d,
        success: function(data) {
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
        },
        error: function(data) {
            alert('An error has occurred:\n' +
                  'URL:    ' + 'REST/names_dates.php\n' +
                  'DATA:   ' + d + '\n' +
                  'FUNCT:  ' + 'graphDates\n' +
                  'STATUS: ' + data.statusText + "\n" +
                  'ERROR:  ' + data.responseJSON);
        }
    });
}

function logout()
{
    $('#logout')[0].submit();
}

function addComment(tablet_group_id) {
    // create a popup window of inputComment.php
    window.open("inputComment.php?tablet_group_id=" + tablet_group_id,
                null, "height=800, width=1600, status=yes,toolbar=no,menubar=no, location=no");
}