
define('advanced:views/dashlets/options/report',
['views/dashlets/options/base', 'advanced:views/report/fields/columns', 'advanced:report-helper'],
function (Dep, Columns, ReportHelper) {

    return Dep.extend({

        template: 'advanced:dashlets/options/report',

        setup: function () {
            if (!this.optionsData.displayType && this.optionsData.type) {
                this.setCorrespondingDisplayType();
            }

            Dep.prototype.setup.call(this);

            this.reportData = {
                entityType: this.optionsData.entityType || null,
                type: this.optionsData.type || null,
                runtimeFilters: this.optionsData.runtimeFilters || null,
                columns: this.optionsData.columns || null,
                depth: this.optionsData.depth || 0,
            };

            this.reportHelper = new ReportHelper(
                this.getMetadata(),
                this.getLanguage(),
                this.getDateTime(),
                this.getConfig(),
                this.getPreferences()
            );

            this.listenTo(this.model, 'change:reportName', model => {
                setTimeout(() => {
                    model.set('title', model.get('reportName'));
                }, 100);
            });

            this.cotrolUseSiMultiplierField();

            this.listenTo(this.model, 'change:displayTotal', (value, model, o) => {
                this.cotrolUseSiMultiplierField();
            });

            this.listenTo(this.model, 'change:displayOnlyCount', (value, model, o) => {
                this.cotrolUseSiMultiplierField();
            });

            this.listenTo(this.model, 'change:reportId', (model) => {
                this.reportData = {};

                this.removeRuntimeFilters();
                this.hideColumnsField();

                this.hideField('useSiMultiplier');

                var reportId = model.get('reportId');

                if (!reportId) {
                    this.controlRuntimeFiltersPanel();

                    return;
                }

                this.getModelFactory().create('Report', model => {
                    model.id = reportId;

                    this.listenToOnce(model, 'sync', () => {
                        var columns = (model.get('columns') || []).filter(item => {
                            return this.reportHelper.isColumnNumeric(item, model.get('entityType'));
                        });

                        var reportData = {
                            entityType: model.get('entityType'),
                            type: model.get('type'),
                            runtimeFilters: model.get('runtimeFilters'),
                            columns: columns,
                        };

                        if (
                            (model.get('type') === 'Grid' || model.get('type') === 'JointGrid') &&
                            model.get('groupBy')
                        ) {
                            reportData.depth = model.get('groupBy').length;
                        } else {
                            reportData.depth = null;
                        }

                        this.model.set('depth', reportData.depth);
                        this.model.set('entityType', model.get('entityType'));

                        var displayType = '';

                        if (reportData.type === 'List') {
                            displayType = 'List';
                        }
                        else if (reportData.type === 'Grid' || reportData.type === 'JointGrid') {
                            displayType = 'Chart';
                        }

                        this.model.set('displayType', displayType);

                        this.model.set('type', reportData.type);

                        this.reportData = reportData;

                        if (this.hasRuntimeFilters()) {
                            this.createRuntimeFilters();
                        }

                        this.controlRuntimeFiltersPanel();

                        this.handleColumnField();
                        this.cotrolUseSiMultiplierField();
                    });

                    model.fetch();
                });
            });
        },

        setCorrespondingDisplayType: function () {
            var type = this.optionsData.type;

            var displayTotal = this.optionsData.displayTotal;
            var displayOnlyCount = this.optionsData.displayOnlyCount;

            if (displayOnlyCount) {
                this.optionsData.displayType = 'Total';

                return;
            }

            if (displayTotal && (type === 'Grid' || type === 'JointGrid')) {
                this.optionsData.displayType = 'Chart-Total';

                return;
            }

            if (type === 'List') {
                this.optionsData.displayType = 'List';

                return;
            }

            if (type === 'Grid' || type === 'JointGrid') {
                this.optionsData.displayType = 'Chart';

                return;
            }
        },

        cotrolUseSiMultiplierField: function () {
            if (this.model.get('displayOnlyCount') || this.model.get('displayTotal')) {
                this.showField('useSiMultiplier');
            } else {
                this.hideField('useSiMultiplier');
            }
        },

        handleColumnField: function () {
            var recordView = this.getView('record');

            this.hideField('displayOnlyCount');

            if (this.reportData.type) {
                this.showField('displayOnlyCount');

                if (this.reportData.type === 'Grid') {
                    this.showField('displayTotal');
                }

                if (this.reportData.type === 'JointGrid') {
                    this.showField('displayTotal');
                }

                if (this.reportData.type === 'List') {
                    this.hideField('displayTotal');
                }
            }

            if (recordView) {
                var columnView = recordView.getFieldView('column');

                if (this.reportData.type === 'Grid') {
                    columnView.params.options = Espo.Utils.clone(this.reportData.columns || []);
                    columnView.translatedOptions = {};

                    Columns.prototype.setupTranslatedOptions.call(columnView);

                    if (
                        (this.reportData.depth === 0 || this.reportData.depth === 1) &&
                        columnView.params.options.length > 1
                    ) {
                        columnView.params.options.unshift('');
                        columnView.translatedOptions[''] = this.translate('All');
                    }

                    this.$el.find('.cell-column').removeClass('hidden');

                    if ('showField' in recordView) {
                        recordView.showField('column');
                    }

                } else {
                    columnView.params.options = [];

                    this.hideColumnsField();
                }

                columnView.render();
            }
        },

        hideColumnsField: function () {
            this.$el.find('.cell-column').addClass('hidden');

            var recordView = this.getView('record');

            if ('hideField' in recordView) {
                recordView.hideField('column');
            }
        },

        afterRender: function () {
            this.handleColumnField();

            if (this.hasRuntimeFilters()) {
                this.createRuntimeFilters();
            }


            this.controlRuntimeFiltersPanel();
        },

        controlRuntimeFiltersPanel: function () {
            let $panel = this.$el.find('.runtime-filters-panel');

            this.hasRuntimeFilters() ?
                $panel.removeClass('hidden') :
                $panel.addClass('hidden');
        },

        hasRuntimeFilters: function () {
            return (this.reportData.runtimeFilters || []).length !== 0
        },

        removeRuntimeFilters: function () {
            this.clearView('runtimeFilters');
        },

        createRuntimeFilters: function () {
            this.createView('runtimeFilters', 'advanced:views/report/runtime-filters', {
                el: this.options.el + ' .runtime-filters-container',
                entityType: this.reportData.entityType,
                filterList: this.reportData.runtimeFilters,
                filtersData: this.optionsData.filtersData || null,
            }, view => {
                view.render();
            });
        },

        fetchAttributes: function () {
            if (this.getView('record').getFieldView('report').validate()) {
                return;
            }

            var attributes = Dep.prototype.fetchAttributes.call(this) || {};

            if (this.hasRuntimeFilters()) {
                var runtimeFiltersView = this.getView('runtimeFilters');

                if (runtimeFiltersView) {
                    attributes.filtersData = runtimeFiltersView.fetchRaw();
                }
            }

            attributes.entityType = this.reportData.entityType;
            attributes.runtimeFilters = this.reportData.runtimeFilters;
            attributes.type = this.reportData.type;
            attributes.columns = this.reportData.columns;
            attributes.depth = this.reportData.depth;

            return attributes;
        },
    });
});
