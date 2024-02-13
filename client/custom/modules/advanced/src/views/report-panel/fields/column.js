
define('advanced:views/report-panel/fields/column',
['views/fields/enum', 'advanced:views/report/fields/columns'], function (Dep, Columns) {

    return Dep.extend({

        setup: function () {
            Dep.prototype.setup.call(this);

            this.listenTo(this.model, 'update-columns', columnList => {
                this.params.options = columnList;
                Columns.prototype.setupTranslatedOptions.call(this, this.model.get('reportEntityType'));

                this.translatedOptions[''] = this.translate('All');

                this.reRender();
            });

            this.listenTo(this.model, 'change:columnList', () => {
                this.model.trigger('update-columns', this.model.get('columnList') || []);
            });
        },

        setupOptions: function () {
            this.params.options = Espo.Utils.clone(this.model.get('columnList'));

            if (
                !this.model.isNew &&
                this.model.get('reportType') === 'Grid' &&
                !this.params.options
            ) {
                this.listenToOnce(this.model, 'sync', () => {
                    if (this.model.get('columnList')) {
                        this.params.options = Espo.Utils.clone(this.model.get('columnList'));
                        Columns.prototype.setupTranslatedOptions.call(this, this.model.get('reportEntityType'));

                        this.translatedOptions[''] = this.translate('All');

                        this.reRender();
                    }
                });
            }

            if (!this.params.options && this.model.get('column')) {
                this.params.options = [this.model.get('column')];
            }

            if (!this.params.options) {
                this.params.options = [];
            }

            Columns.prototype.setupTranslatedOptions.call(this, this.model.get('reportEntityType'));

            this.translatedOptions[''] = this.translate('All');
        },
    });
});
