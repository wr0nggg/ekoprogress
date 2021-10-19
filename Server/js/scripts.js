document.createElement('script').src = 'https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js';
document.createElement('script').src = 'https://cdn.datatables.net/1.11.2/js/jquery.dataTables.min.js';

class Plot {
  constructor(json) {
    this.canvasID = json.canvasID;
    this.covID = json.covID;
    this.variableType = json.variableType;
    this.unit = json.unit;
    this.backgroundColor = json.backgroundColor;
    this.color = json.color;
    this.chart = null;
  }

  CreateChart(labels, values, timePeriod) {
    let ctx = document.getElementById(this.canvasID).getContext('2d');
    this.chart = new Chart(ctx, {
      type: (timePeriod == "day" ? "bar" : "line"),
      data: {
        labels: labels,
        datasets: [
          {
            data: values,
            backgroundColor: this.backgroundColor + '9F',
            borderColor: this.backgroundColor,
            borderWidth: 2,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: false
        },
        scales: {
          x: {
            barPercentage: 1,
            ticks: {
              font: {
                size: 10,
              },
              color: this.color
              //maxTicksLimit: 8
            },
            grid: {
              color: "#40535A",
              borderDash: [4, 4],
            }
          },
          y: {
            ticks: {
              beginAtZero: true,
              color: this.color
            },
            grid: {
              display: false,
              color: "#40535A"
            }
          }
        }
      }
    });
  }

  Update(timePeriod, color) {
    this.color = color;
    let self = this;
    $.ajax({
      method: "POST",
      async: true,
      url: "updateplot.php",
      data: {
        'covID': self.covID,
        'timePeriod': timePeriod,
        'variableType': self.variableType
      },
      dataType: 'json',
      success: function (msg) {
        let labels = msg.labels;
        let values = msg.values;
        let graph = Chart.getChart(self.canvasID);
        if (graph instanceof Chart) graph.destroy();
        self.CreateChart(labels, values, timePeriod);
      }
    });
  }

  ColorMode(color) {
    this.chart.options.scales.x.ticks.color = color;
    this.chart.options.scales.y.ticks.color = color;
    this.chart.update();
  }
}


class Title {
  constructor(json) {
    this.sumTitleID = json.sumTitleID;
    this.avgTitleID = json.avgTitleID;
    this.covID = json.covID;
    this.variableType = json.variableType;
    this.unit = json.unit;
  }

  CreateTitle(value, timePeriod, prefix) {
    if (timePeriod == 'day') timePeriod = 'DEŇ';
    else if (timePeriod == 'month') timePeriod = 'MESIAC';
    else if (timePeriod == 'year') timePeriod = 'ROK';
    let title = prefix + " ZA " + timePeriod + ": ";
    title += value + " " + this.unit;
    return title;
  }

  Update(timePeriod, func) {
    let self = this;
    $.ajax({
      method: "POST",
      async: true,
      url: "updatetitle.php",
      data: {
        'covID': self.covID,
        'timePeriod': timePeriod,
        'variableType': self.variableType,
        'func': func
      },
      dataType: 'json',
      success: function (msg) {
        let value = msg.value[0];
        if (func == "AVG") document.getElementById(self.avgTitleID).innerHTML = self.CreateTitle(value, timePeriod, "PRIEMER");
        else if (func == "SUM") document.getElementById(self.sumTitleID).innerHTML = self.CreateTitle(value, timePeriod, "SÚČET");
      }
    });
  }
}


class Table {
  constructor(json) {
    this.table = json.table;
    this.days = json.days;
    this.titles = json.headers;
  }

  Draw(dataset) {
    let self = this;
    $('#'+this.table).DataTable({
      order: [[0, "desc"]],
      searching: false,
      paging: false,
      info: false,
      data: dataset,
      destroy: true,
      dataType: "json",
      columns: [
        { title: self.titles[0] },
        { title: self.titles[1] },
        { title: self.titles[2] }
      ]
    });
  }

  GetContent(update) {
    let self = this;
    $.ajax({
      method: "POST",
      async: true,
      url: "updatetable.php",
      data: {
        'table': this.table,
        'days': this.days,
        'update': update
      },
      dataType: "text",
      success: function (msg) {
        let dataset = JSON.parse(msg.split('</html>')[1]);
        self.Draw(dataset);
      }
    });
  }

  Destroy() {
    if($.fn.dataTable.isDataTable('#'+this.table)) $('#'+this.table).DataTable().destroy();
  }

  Update() {
    this.DrawTables(this.GetDataset(0));
  }
}