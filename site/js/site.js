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

function getTablet(tabletID) {
    $.getJSON("./REST/tablet.php", "tabletID=" + tabletID, printTablet);
}

function printDates(data) {
    console.log(data);
}

function getDates(search) {
    $.getJSON("./REST/dates.php", "search=" + search, function(data) {
        console.log(data);
        
        var max = 1;
        for(var i = 0; i < data.length; ++i) {
            if(parseInt(data[i].count) > max) {
                max = parseInt(data[i].count);
            }
        }
        console.log(max);
        var text = "<table style=\"position: fixed; right: 0; font-size: 3px; top: 100px; width: 50px;\">";
        for(var i = 0; i < data.length; ++i) {
            var value = 255 - Math.floor(parseInt(data[i].count) * 255 / max);
            var string = value.toString(16);
            if(string.length == 1) {
                string = "0" + string;
            }
            console.log(string);
            text += "<tr><td style=\"height: 3px; background-color:#" + string + string + "FF;\">" + "</td></tr>";
        }
        text += "</table>";
        $("body").append(text);
        
    });
}