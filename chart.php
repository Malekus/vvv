<?php

$breaks = "breaksVille";
$models = ['Stains', 'Epinay-sur-seine', 'Bagnolet'];
$categories = $values = [];

foreach ($models as $model) {
    $tmp = DB::table('eleves')
        ->where('ville', '=', $model)
        ->where('updated_at', 'like', '%'. $date .'%')
        ->count();
    array_push($categories, ucfirst($model));
    array_push($values, $tmp);
}

$chart = "var ".$breaks." = new Map();\n    Highcharts.chart('" . $idGraphe . "', {
                        chart: {
                            type: 'column',
                            events: {
                            render: function() {
                              let series = this.series
                              let sum = 0
                              for(let i = 0; i < series.length; i++) {
                                if(series[i].visible){
                                  for(let j = 0; j < series[i].data.length; j++) {
                                    sum += series[i].data[j].y
                                  }
                                }
                              }
                            this.setTitle(false, {text: sum + ' exclus'}, false) 
                            }
                          }
                        },
                        title: {
                            text: 'Nombre d\'élèves exclus',
                        },
                        xAxis: {
                           type: 'category',
                        },
                        yAxis: {
                            min: 0,
                            
                            title: {
                                text: ''
                            }
                        },
                        plotOptions: {
                            column: {
                                grouping: false,
                                pointPadding: 0.2,
                                borderWidth: 0,
                                events: {
                                    legendItemClick: function() {
                                        if(".$breaks.".has(this.xData[0])) {
                                        ".$breaks.".delete(this.xData[0])
                                      }
                                      else {
                                        ".$breaks.".set(this.xData[0], {from: this.xData[0] - 0.5,to: this.xData[0] + 0.5,breakSize: 0})
                                      }
                                      this.chart.xAxis[0].update({
                                        breaks: [... ".$breaks.".values()]
                                      });
                                    }
      							}
                            },
                            series: {
                                dataLabels: { enabled: true }
                            }
                        },
                        series: ".$this->getSeries($categories, $values).",
                        credits: { enabled: false },
                        tooltip: { enabled: false },
                        });";
