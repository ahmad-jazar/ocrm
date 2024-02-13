
define('advanced:views/report-panel/fields/report',
['views/fields/link', 'advanced:report-helper'], function (Dep, ReportHelper) {

    return Dep.extend({

        createDisabled: true,

        setup: function () {
            Dep.prototype.setup.call(this);

            this.reportHelper = new ReportHelper(
                this.getMetadata(),
                this.getLanguage(),
                this.getDateTime(),
                this.getConfig(),
                this.getPreferences()
            );
        },

        select: function (model) {
            this.model.set('reportType', model.get('type'), {isManual: true});
            this.model.set('reportEntityType', model.get('entityType'));

            if (model.get('type') !== 'Grid') {
                if (model.get('type') === 'List') {
                    this.model.set('displayTotal', false);
                }

                this.model.set('column', null);
            }
            else {
                var column = null;
                var columns = model.get('columns') || [];

                if (columns.length) {
                    column = columns[0];
                }

                columns = columns.filter(item => {
                    return this.reportHelper.isColumnNumeric(item, model.get('entityType'));
                });

                if ((model.get('groupBy') || []).length < 2 && columns.length > 1) {
                    columns.unshift('');
                }

                this.model.set('column', column);
                this.model.trigger('update-columns', columns);
            }

            Dep.prototype.select.call(this, model);
        },

        clearLink: function () {
            Dep.prototype.clearLink.call(this);
            this.model.set('reportType', null, {isManual: true});
            this.model.set('displayTotal', false);
        }
    });
});
