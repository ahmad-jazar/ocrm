
define('advanced:views/report/reports/charts/grid2bar-grouped-vertical',
['advanced:views/report/reports/charts/grid2bar-vertical'], function (Dep) {

    return Dep.extend({

        columnWidth: 60,

        isGrouped: true,

        pointXHalfWidth: 0.5,
    });
});
