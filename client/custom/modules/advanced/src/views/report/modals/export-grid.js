
define('advanced:views/report/modals/export-grid', ['views/modal', 'model'], function (Dep, Model) {

    return Dep.extend({

        templateContent: '<div class="record">{{{record}}}</div>',

        setup: function () {
            this.buttonList = [
                {
                    name: 'export',
                    label: 'Export',
                    style: 'danger',
                },
                {
                    name: 'cancel',
                    label: 'Cancel',
                }
            ];

            this.model = new Model();
            this.model.name = 'Report';

            this.scope = this.options.scope;

            let exportFormat = (this.getMetadata().get('app.export.gridReportFormatList') || [])[0];

            this.model.set('exportFormat', exportFormat);

            this.createView('record', 'advanced:views/report/record/export-grid', {
                scope: this.scope,
                model: this.model,
                el: this.getSelector() + ' .record',
                columnList: this.options.columnList,
                columnsTranslation: this.options.columnsTranslation,
            });
        },

        actionExport: function () {
            let data = this.getView('record').fetch();

            this.model.set(data);

            if (this.getView('record').validate()) {
                return;
            }

            let returnData = {
                format: data.exportFormat,
                column: data.column,
            };

            this.trigger('proceed', returnData);
            this.close();
        },
    });
});
