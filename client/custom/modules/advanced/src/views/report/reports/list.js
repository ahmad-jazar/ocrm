
define('advanced:views/report/reports/list', ['advanced:views/report/reports/base'], function (Dep) {

    return Dep.extend({

        setup: function () {
            this.initReport();
        },

        getListLayout: function (forExport) {
            let scope = this.model.get('entityType')
            let layout = [];

            let columnsData = Espo.Utils.cloneDeep(this.model.get('columnsData') || {});

            (this.model.get('columns') || []).forEach(item => {
                let o = columnsData[item] || {};
                o.name = item;

                if (!forExport && o.exportOnly) {
                    return;
                }

                if (~item.indexOf('.')) {
                    let a = item.split('.');
                    o.name = item.replace('.', '_');
                    o.notSortable = true;

                    let link = a[0];
                    let field = a[1];

                    let foreignScope = this.getMetadata().get('entityDefs.' + scope + '.links.' + link + '.entity');

                    o.customLabel = this.translate(link, 'links', scope) + '.' +
                        this.translate(field, 'fields', foreignScope);

                    let type = this.getMetadata().get('entityDefs.' + foreignScope + '.fields.' + field + '.type');

                    if (type === 'enum') {
                        o.view = 'advanced:views/fields/foreign-enum';
                        o.options = {
                            foreignScope: foreignScope
                        };
                    } else if (type === 'image') {
                        o.view = 'views/fields/image';
                        o.options = {
                            foreignScope: foreignScope
                        };
                    } else if (type === 'file') {
                        o.view = 'views/fields/file';
                        o.options = {
                            foreignScope: foreignScope
                        };
                    } else if (type === 'date') {
                        o.view = 'views/fields/date';
                        o.notSortable = false;
                        o.options = {
                            foreignScope: foreignScope
                        };
                    } else if (type === 'datetime') {
                        o.view = 'views/fields/datetime';
                        o.options = {
                            foreignScope: foreignScope
                        };
                        o.notSortable = false;
                    }
                    else if (type === 'link') {
                        o.view = 'advanced:views/fields/foreign-link';
                    }
                    else if (type === 'email') {
                        o.view = 'views/fields/email';
                        o.notSortable = false;
                    }
                    else if (type === 'phone') {
                        o.view = 'views/fields/phone';
                        o.notSortable = false;
                    }
                    else if (
                        type === 'array' ||
                        type === 'checklist' ||
                        type === 'multiEnum'
                    ) {
                        o.view = 'views/fields/array';
                        o.notSortable = true;
                    }
                    else if (type === 'urlMultiple') {
                        o.view = 'views/fields/url-multiple';
                        o.notSortable = true;
                    }
                    else if (type === 'varchar') {
                        o.view = 'views/fields/varchar';
                        o.notSortable = false;
                    }
                    else if (type === 'bool') {
                        o.view = 'views/fields/bool';
                        o.notSortable = false;
                    }
                    else if (type === 'currencyConverted') {
                        o.view = 'views/fields/currency-converted';
                        o.notSortable = false;
                    }
                } else {
                    let type = this.getMetadata().get(['entityDefs', scope, 'fields', item, 'type']);

                    if (type === 'linkMultiple') {
                        o.notSortable = true;
                    } else if (type === 'attachmentMultiple') {
                        o.notSortable = true;
                    }
                }

                layout.push(o);
            });

            return layout;
        },

        export: function () {
            let where = this.getRuntimeFilters();

            let url = 'Report/action/exportList';

            let data = {
                id: this.model.id,
            };

            let fieldList = [];

            let listLayout = this.getListLayout(true);

            listLayout.forEach(item => {
                fieldList.push(item.name);
            });

            let o = {
                fieldList: fieldList,
                scope: this.model.get('entityType'),
            };

            this.createView('dialogExport', 'views/export/modals/export', o, (view) => {
                view.render();

                this.listenToOnce(view, 'proceed', (dialogData) => {
                    if (!dialogData.exportAllFields) {
                        data.attributeList = dialogData.attributeList;
                        data.fieldList = dialogData.fieldList;
                    }

                    data.where = where;
                    data.format = dialogData.format;
                    data.params = dialogData.params;

                    Espo.Ui.notify(' ... ');

                    Espo.Ajax.postRequest(url, data, {timeout: 0})
                        .then(data => {
                            Espo.Ui.notify(false);

                            if ('id' in data) {
                                window.location = this.getBasePath() + '?entryPoint=download&id=' + data.id;
                            }
                        });
                });
            });
        },

        run: function () {
            Espo.Ui.notify(' ... ');

            let $container = this.$el.find('.report-results-container');
            $container.empty();

            let $listContainer = $('<div>').addClass('report-list');

            $container.append($listContainer);

            this.getCollectionFactory().create(this.model.get('entityType'), (collection) => {
                collection.url = 'Report/action/runList?id=' + this.model.id;
                collection.where = this.getRuntimeFilters();

                let orderByList = this.model.get('orderByList');

                if (orderByList && orderByList !== '') {
                    let arr = orderByList.split(':');

                    if (collection.setOrder) {
                        collection.setOrder(arr[1], arr[0] === 'ASC' ? 'asc' : 'desc', true);
                    }
                }

                collection.maxSize = this.getConfig().get('recordsPerPage') || 20;

                this.listenToOnce(collection, 'sync', () => {
                    this.storeRuntimeFilters();

                    this.createView('list', 'advanced:views/record/list-for-report', {
                        el: this.options.el + ' .report-list',
                        collection: collection,
                        listLayout: this.getListLayout(),
                        displayTotalCount: true,
                        reportId: this.model.id,
                        runtimeWhere: collection.where,
                    }, view => {
                        Espo.Ui.notify(false);

                        this.listenTo(view, 'after:render', () => {
                            view.$el.find('> .list')
                                .addClass('no-side-margin')
                                .addClass('no-bottom-margin')
                                .addClass('bottom-border-radius');

                            view.$el.find('.list-buttons-container').addClass('margin-bottom');
                        });

                        view.render();
                    });
                });

                collection.fetch();
            });
        },
    });
});
