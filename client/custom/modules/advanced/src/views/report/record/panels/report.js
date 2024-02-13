
define('advanced:views/report/record/panels/report', ['view', 'advanced:report-helper'], function (Dep, ReportHelper) {

    return Dep.extend({

        template: 'advanced:report/record/panels/report',

        setup: function () {
            var reportHelper = new ReportHelper(
                this.getMetadata(),
                this.getLanguage(),
                this.getDateTime(),
                this.getConfig(),
                this.getPreferences()
            );

            var viewName = reportHelper.getReportView(this.model);

            this.createView('report', viewName, {
                el: this.options.el + ' .report-container',
                model: this.model,
                reportHelper: reportHelper,
            });
        },

        actionRefresh: function () {
            var reportView = this.getView('report');
            reportView.run();
        },
    });
});
