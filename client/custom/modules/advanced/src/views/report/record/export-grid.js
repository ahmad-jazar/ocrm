
define('advanced:views/report/record/export-grid', ['views/record/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:report/record/export-grid',

        setup: function () {
            Dep.prototype.setup.call(this);

            this.scope = this.options.scope;

            var gridReportFormatList = this.getMetadata().get('app.export.gridReportFormatList') || [];

            var version = this.getConfig().get('version') || '';
            var arr = version.split('.');

            if (version !== 'dev' && arr.length > 2 && parseInt(arr[0]) * 100 + parseInt(arr[1]) < 407) {
                gridReportFormatList = ['csv'];
            }

            this.createField('exportFormat', 'views/fields/enum', {
                options: gridReportFormatList,
            });

            this.controlColumnField();
            this.listenTo(this.model, 'change:exportFormat', this.controlColumnField, this);

            if (this.options.columnList) {
                this.createField('column', 'views/fields/enum', {
                    options: this.options.columnList,
                    translatedOptions: this.options.columnsTranslation || {}
                });
            }
        },

        controlColumnField: function () {
            if (this.model.get('exportFormat') === 'csv') {
                this.showField('column');
            } else {
                this.hideField('column');
            }
        },
    });
});
