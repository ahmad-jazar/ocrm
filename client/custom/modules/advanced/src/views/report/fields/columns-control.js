
define('advanced:views/report/fields/columns-control', ['views/fields/base', 'model'], function (Dep, Model) {

    return Dep.extend({

        editTemplate: 'advanced:report/fields/filters-control/edit',
        detailTemplate: 'advanced:report/fields/filters-control/detail',

        fieldDataList: [
            {
                name: 'width',
                view: 'views/fields/float',
            },
            {
                name: 'align',
                view: 'views/fields/enum',
                params: {
                    options: ["left", "right"],
                    translation: 'Report.options.layoutAlign',
                },
            },
            {
                name: 'link',
                view: 'views/fields/bool',
            },
            {
                name: 'exportOnly',
                view: 'views/fields/bool',
            },
            {
                name: 'notSortable',
                view: 'views/fields/bool',
            },
        ],

        setup: function () {
            this.entityType = this.model.get('entityType');

            this.seed = new Model();
            this.seed.name = 'LayoutManager';

            this.setupColumnsData();

            this.listenTo(this.model, 'change:columns', () => {
                var previousColumnList = Espo.Utils.clone(this.columnList);
                var toAdd = [];
                var toRemove = [];

                this.setupColumnsData();

                var columnList = this.columnList;

                columnList.forEach(name => {
                    if (!~previousColumnList.indexOf(name)) {
                        toAdd.push(name);
                    }
                });

                previousColumnList.forEach(name => {
                    if (!~columnList.indexOf(name)) {
                        toRemove.push(name);
                    }
                });

                if (this.isRendered()) {
                    toAdd.forEach(name => {
                        this.createColumn(name);
                    });

                    toRemove.forEach(name => {
                        this.removeColumn(name);
                    });
                }
            });
        },

        setupColumnsData: function () {
            this.columnList = Espo.Utils.clone(this.model.get('columns')) || [];
        },

        afterRender: function () {
            this.columnList.forEach(name => {
                var params = (this.model.get('columnsData') || {})[name];

                this.createColumn(name, params);
            });
        },

        removeColumn: function (name) {
            this.clearView('name-' + name);
            this.$el.find('.filters-row .column-' + Espo.Utils.toDom(name)).remove();

            this.fieldDataList.forEach(item => {
                this.clearView('field-'+name+'-'+item.name);
            });
        },

        createColumn: function (name) {
            var label;

            if (~name.indexOf('.')) {
                var link = name.split('.')[0];
                let field = name.split('.')[1];

                var scope = this.getMetadata().get('entityDefs.' + this.entityType + '.links.' + link + '.entity');

                label = this.translate(link, 'links', this.entityType) + '.' + this.translate(field, 'fields', scope);
            } else {
                label = this.translate(name, 'fields', this.entityType);
            }

            var columnHtml =
                '<div class="column column-' + Espo.Utils.toDom(name) + ' col-sm-4 col-md-3" data-name="'+name+'">' +
                '<label class="cotrol-label">'+label+'</label>' +
                '<div class="column-content" style="margin-bottom: 10px;">' + '</div>' +
                '</div>';

            let $column = $(columnHtml);
            this.$el.find('.filters-row').append($column);

            $column = this.$el.find('.filters-row .column-' + Espo.Utils.toDom(name));
            var $columnContent = $column.find('.column-content');

            var model = this.seed.clone();
            model.name = 'LayoutManager';

            var defaultAttributes = {};

            if (name === 'name') {
                defaultAttributes.link = true;
            }

            var attr = (this.model.get('columnsData') || {})[name] || defaultAttributes;
            model.set(attr);

            this.fieldDataList.forEach(item => {
                var fieldHtml =
                    '<div class="column-field" data-name="'+item.name+'">' +
                        '<label class="cotrol-label small">' +
                            this.translate(item.name, 'layoutFields', 'Report')+'</label>' +
                        '<div class="field-content"></div>' +
                    '</div>';

                var $field = $(fieldHtml);

                $columnContent.append($field);

                this.createView('field-'+name+'-'+item.name, item.view, {
                    model: model,
                    name: item.name,
                    defs: {
                        name: item.name,
                        params: item.params || {},
                    },
                    el: this.options.el + ' .column[data-name="'+name+'"] ' +
                        '.column-content .column-field[data-name="'+item.name+'"] .field-content',
                    mode: 'edit',
                }, view => {
                    view.render();
                });
            });
        },

        fetch: function () {
            var data = {};

            this.columnList.forEach(name => {
                var columnData = {};

                this.fieldDataList.forEach(item => {
                    var view = this.getView('field-'+name+'-'+item.name);

                    if (!view) {
                        return;
                    }

                    columnData = _.extend(columnData, view.fetch());
                });

                data[name] = columnData;
            });

            return {'columnsData': data};
        },
    });
});
