document.write(
    '<script type="text/javascript" src="/Public/js/highcharts/highcharts.js"></script>' +
    '<script type="text/javascript" src="/Public/js/highcharts/highcharts-zh_CN.js"></script>'
);

var chart = null;

$(function ()
{
    var title = {y:parseFloat(parseFloat($('#container').height() / 2) + 4) ,floating:true,style: {color:'#999',fontFamily:'PingFang SC',fontSize:'4vw',fontWeight:'bold'}};

    var analysis = $('.index-analysis');

    if(releaseNumber == 0 && handleNumber == 0 && completeNumber == 0)
    {
        title.text = 'No data'
    }
    else
    {
        title.text = parseInt(releaseNumber);

        title.style.color = '#0787f6';
    }

    var chart = Highcharts.chart('container',
    {
        chart:
        {
            spacing : [0, 0 , 0, -150],
            height: parseFloat(analysis.height()),
            type:'pie'
        },
        title: title,
        tooltip://数据提示框
        {
            enabled:false,
            pointFormat: '<b>{point.percentage:.1f}%</b>'
        },
        legend:
        {
            layout: 'vertical',
            backgroundColor: '#fff',
            floating: true,
            align:'right',
            verticalAlign: 'middle',
            itemMarginTop: 0,
            itemMarginBottom:10,
            itemStyle: {
                color: '#333',
                fontFamily:'PingFang SC',
                fontSize:'3.5vw'
            },
            x:-20
        },
        plotOptions:
        {
            pie:
            {
                size:parseFloat(analysis.width()) / 3,
                colors:["#0787f6","#fcac31","#2c6ee5"],
                allowPointSelect: false,//选中某块区域是否允许分离
                showInLegend: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: false,//是否直接呈现数据 也就是外围显示数据与否
                    format: '<b>{point.name}</b>: {point.percentage:.1f} %',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
                    }
                },
                point:
                {
                    events:
                        {
                            click: function(e) { // 同样的可以在点击事件里处理
                                chart.setTitle({
//                                    text: e.point.percentage.toFixed(1)+'%',
                                    text: this.y,
                                    style: {color:e.point.color,fontFamily:'PingFang SC',fontSize:'4vw'},
                                    y:parseFloat(parseFloat($('#container').height() / 2) + 4)
                                });
                            }
                        }
                }
            }
        },
        series:
        [{
            type: 'pie',
            innerSize: '65%',
            name: ' ',
            data:
            [
                {name:language.TODAY_RELEASE_TICKET,y: parseFloat(releaseNumber)},
                {name:language.TODAY_PROCESS_TICKET,y: parseFloat(handleNumber)},
                {name:language.TODAY_COMPLETE_TICKET,y: parseFloat(completeNumber)}
            ]
        }],
        credits:
        {
            enabled:false // 禁用版权信息
        }
    }, function(c) { // 图表初始化完毕后的会掉函数
        // 环形图圆心
        var centerX = c.series[0].center[0];
        var centerY = c.series[0].center[1];

        // 标题字体大小，返回类似 16px ，所以需要 parseInt 处理
        var titleHeight = parseInt(c.title.styles.fontSize);

        // 设置图表偏移
        c.setTitle({
            y: centerY + titleHeight/2
        });
    });
});
