graphe1 = new Map()
Highcharts.chart('idGraphe', {
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
        text: 'les Activités',
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
                    if(graphe1.has(this.xData[0])) {
                        graphe1.delete(this.xData[0])
                    }
                    else {
                    graphe1.set(this.xData[0], {from: this.xData[0] - 0.5,to: this.xData[0] + 0.5,breakSize: 0})
                    }
                    this.chart.xAxis[0].update({
                    breaks: [... graphe1.values()]
                    });
                }
            }
        },
        series: {
            dataLabels: { enabled: true }
        }
    },
    series: [{
        name: 'Installation',
        data: [{
            name : 'Installation',
            y :50
        }]
    },
    {
        name: 'OUIUOUI',
        data: [{
            name : 'OUIUOUI',
            y :54
        }]
    }
],
    credits: { enabled: false },
    tooltip: { enabled: false },
});