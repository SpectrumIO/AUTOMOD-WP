var ONE_DAY = 24 * 60 * 60 * 1000;
var THIRTY_DAYS = 30 * ONE_DAY;

var MONTHS = [
  'Jan',
  'Feb',
  'Mar',
  'Apr',
  'May',
  'Jun',
  'Jul',
  'Aug',
  'Sep',
  'Oct',
  'Nov',
  'Dec'
];

jQuery(function ($) {
  $(document).ready(function () {
    if (!window.Chartist) {
      return;
    }

    if (!window.analyticsData || !window.analyticsData.length) {
      return;
    }

    var labels = [];
    var series = [[]];

    window.analyticsData.forEach(function (res, i) {
      labels.push(res.day);
      series[0].push(res.count)
    });

    var now = Date.now();

    while (now - labels[0] <= THIRTY_DAYS) {
      labels.unshift(labels[0] - ONE_DAY);
      series[0].unshift(null);
    }

    var data = {
      labels: labels,
      series: series
    };

    var el = document.getElementById('analytics-container');
    el.innerHTML = '';

    new Chartist.Bar(el, data, {
      stackBars: true,
      axisX: {
        labelInterpolationFnc: function (value, index) {
          if (index % 2 === 0) {
            return ' ';
          }

          var date = new Date(value);

          return MONTHS[date.getMonth()] + ' ' + (date.getDay() + 1);
        }
      }
    });

    var legendEl = document.getElementById('legend');
    legendEl.classList.add('visible');
  });
});