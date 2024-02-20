const renderStatistics = (formats, organisations, has_links=true) => {
    window.addEventListener('load', (event) => {
        let options = {
            parsing: {
                xAxisKey: 'total',
                yAxisKey: 'total'
            },
            onHover: (event, chartElement) => {
                const target = event.native ? event.native.target : event.target;
                if(has_links){
                    target.style.cursor = chartElement[0] ? 'pointer' : 'default';
                }
            },
            events: ['mousemove', 'mouseout', 'click', 'touchstart', 'touchmove', 'hover'],
            onClick: (event, items) => {
                let chart = event.chart;
                let item = items[0]
                let index = item.index;
                let datasetIndex = item.datasetIndex;
                let itemData = chart.data.datasets[datasetIndex].data[index];
                if(itemData.url && has_links){
                    window.open(itemData.url, "_self");
                }
            }
        }
        window.charts.renderBarChart('formatsChart', formats, options);
        window.charts.renderBarChart('organisationStat', organisations, options);
    });
}
