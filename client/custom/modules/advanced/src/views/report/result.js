
define('advanced:views/report/result', ['views/main', 'advanced:report-helper'], function (Dep, ReportHelper) {

    return Dep.extend({

        template: 'advanced:report/result',

        name: 'result',

        setup: function () {
            var reportHelper = new ReportHelper(
                this.getMetadata(),
                this.getLanguage(),
                this.getDateTime(),
                this.getConfig(),
                this.getPreferences()
            );

            var viewName = reportHelper.getReportView(this.model);

            this.setupHeader();

            this.createView('report', viewName, {
                el: this.options.el + ' .report-container',
                model: this.model,
                reportHelper: reportHelper,
                showChartFirst: true,
                isLargeMode: true,
            });
        },

        setupHeader: function () {
            this.createView('header', 'views/header', {
                model: this.model,
                el: '#main > .header',
                scope: this.scope
            });
        },

        getHeader: function () {
            var name = Handlebars.Utils.escapeExpression(this.model.get('name'));

            if (name === '') {
                name = this.model.id;
            }

            var rootUrl = this.options.rootUrl || this.options.params.rootUrl || '#' + this.scope;

            var headerIconHtml = this.getHeaderIconHtml();

            return this.buildHeaderHtml([
                headerIconHtml + '<a href="' + rootUrl + '" class="action" data-action="navigateToRoot">' + this.getLanguage().translate(this.scope, 'scopeNamesPlural') + '</a>',
               '<a href="#' + this.scope + '/view/' + this.model.id + '" class="action" data-action="backToView">' + name + '</a>'
            ]);
        },

        actionBackToView: function () {
            var options = {
                id: this.model.id,
                model: this.model,
            };

            options.rootUrl = this.options.rootUrl || this.options.params.rootUrl;

            this.getRouter().navigate('#' + this.scope + '/view/' + this.model.id, {trigger: false});
            this.getRouter().dispatch(this.scope, 'view', options);
        },

    });
});
