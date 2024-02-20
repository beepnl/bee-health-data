import { isArray } from 'lodash';

const Chart = require('chart.js/auto').default;

const DefaultColors = [
    {
        backgroundColor: 'rgba(255, 99, 132, 0.2)',
        borderColor: 'rgba(255, 99, 132, 1)'
    },
    {
        backgroundColor: 'rgba(54, 162, 235, 0.2)',
        borderColor: 'rgba(54, 162, 235, 1)'
    },
    {
        backgroundColor: 'rgba(255, 206, 86, 0.2)',
        borderColor: 'rgba(255, 206, 86, 1)'
    },
    {
        backgroundColor: 'rgba(75, 192, 192, 0.2)',
        borderColor: 'rgba(75, 192, 192, 1)'
    },
    {
        backgroundColor: 'rgba(153, 102, 255, 0.2)',
        borderColor: 'rgba(153, 102, 255, 1)'
    },
    {
        backgroundColor: 'rgba(255, 159, 64, 0.2)',
        borderColor: 'rgba(255, 159, 64, 1)'
    }
];

const generateColor = function() {
    return DefaultColors[Math.floor(Math.random() * DefaultColors.length)];
}

const generateColors = function(number, colors = [], prevColor = null){
    let color = generateColor();
    if(number == 0){
        return colors;
    }

    if(JSON.stringify(color) == JSON.stringify(prevColor)){
        return generateColors(number, colors, prevColor = color)
    }else{
        colors.push(color)
        return generateColors(number - 1, colors, prevColor = color)
    }

}

export function renderBarChart(elementId, data = {}, options = {}){
    const ctx = document.getElementById(elementId).getContext('2d');
    let _dataset = {
        backgroundColor: [],
        borderColor: []
    }
    if(data.labels){
        let _colors = generateColors(data.labels.length);
        _colors.forEach(({backgroundColor, borderColor}) => {
            _dataset['backgroundColor'].push(backgroundColor)
            _dataset['borderColor'].push(borderColor)
            _dataset['borderWidth'] = 1
        });
    }

    if(data.datasets){
        data.datasets.forEach(dataset => {
            return Object.assign(dataset, _dataset)
        });
    }

    let defaultOptions = {
        type: 'bar',
        data: {
            
        },
        options: {
            ...Object.assign({
                animation: {
                    // "onComplete": function() {
                    //     var chartInstance = this,
                    //     ctx = this.ctx;
                    //     chartInstance.data.datasets.forEach(function(dataset, i) {
                    //         var meta = chartInstance.getDatasetMeta(i);
                    //         meta.data.forEach(function(bar, index) {
                    //             var data = dataset.data[index];
                    //             ctx.fillText(data, bar.x + 10, bar.y);
                    //         });
                    //     });
                    // }
                },
                indexAxis: 'y',
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            display: false,
                        },
                        // display: false
                    },
                    x: {
                    
                        grid: {
                            display: false
                        },
                        // display: false
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    
                }
            }, options)
            
        }
    }
    let _options = Object.assign({}, defaultOptions, {data: data});
    return  new Chart(ctx, _options);
}
