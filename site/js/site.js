function printTablet(data) {
    console.log(data);
    var value = "<div class=\"panel panel-default\">\n" + "<div class = \"panel-heading\">" + data.name + "</div>\n" + "<div class = \"panel-body\">";
    for (var i = 0; i < data.objects.length; ++i) {
        value += printObject(data.objects[i]);
    }
    value += "</div></div>";
    $("#tablet-output").append(value);
}

function printObject(obj) {
    var value = "<div class=\"panel panel-default\">\n" +
            "<div class = \"panel-heading\">" + obj.name + "</div>\n" +
            "<div class = \"panel-body\">";
    for (var i = 0; i < obj.sections.length; ++i) {
        value += printTextSection(obj.sections[i]);
    }
    value += "</div></div>";
    return value;
}

function printTextSection(section) {
    var value = "<div class=\"panel panel-default\">\n" +
            "<div class = \"panel-heading\">" + section.name + "</div>\n" +
            "<div class = \"panel-body\">" + "<ol>";
    for (var i = 0; i < section.lines.length; ++i) {
        value += "<li>" + section.lines[i] + "</li>";
    }

    value += "</ol></div></div>";
    return value;
}

function getTablet(tablet_id) {
    $.getJSON("./REST/tablet.php", "tablet_id=" + tablet_id, printTablet);
}

function printDates(data) {
    console.log(data);
}



function search(query) {
    $.getJSON("./REST/search.php", "search=" + query, function(data) {
        console.log(data);
        for (var i = 0; i < data.results.length; ++i) {
            getTablet(data.results[i].tablet_id);
        }
    });
}

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

        var chart = new google.visualization.ColumnChart(document.getElementById('date_chart_div'));
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
            'hAxis.slantedText': true,
            'hAxis.slantedTextAngle': 90
        };

        var chart = new google.visualization.ColumnChart(document.getElementById('name_chart_div'));
        chart.draw(chartData, options);
    });
}
