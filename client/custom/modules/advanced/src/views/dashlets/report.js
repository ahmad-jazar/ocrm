
define('advanced:views/dashlets/report', ['views/dashlets/abstract/base', 'search-manager', 'advanced:report-helper'],
function (Dep, SearchManager, ReportHelper) {

    return Dep.extend({

        name: 'Report',

        optionsView: 'advanced:views/dashlets/options/report',

        templateContent: '<div class="report-results-container" style="height: 100%;"></div>',

        totalFontSizeMultiplier: 1.5,
        totalLineHeightMultiplier: 1.1,
        totalMarginMultiplier: 0.4,
        totalOnlyFontSizeMultiplier: 4,
        totalLabelMultiplier: 0.6,
        total2LabelMultiplier: 0.4,

        rowActionsView: false,

        setup: function () {
            this.optionsFields['report'] = {
                type: 'link',
                entity: 'Report',
                required: true,
                view: 'advanced:views/report/fields/dashlet-select'
            };

            this.optionsFields['column'] = {
                'type': 'enum',
                'options': []
            };

            this.reportHelper = new ReportHelper(
                this.getMetadata(),
                this.getLanguage(),
                this.getDateTime(),
                this.getConfig(),
                this.getPreferences()
            );
        },

        afterAdding: function () {
            this.getParentView().actionOptions();
        },

        getListLayout: function () {
            let scope = this.getOption('entityType')
            let layout = [];

            let columnsData = Espo.Utils.cloneDeep(this.columnsData || {});

            (this.columns || []).forEach(item => {
                let o = columnsData[item] || {};
                o.name = item;

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
                        o.options = {
                            foreignScope: foreignScope
                        };
                    } else if (type === 'datetime') {
                        o.view = 'views/fields/datetime';
                        o.options = {
                            foreignScope: foreignScope
                        };
                    }
                }

                layout.push(o);
            });

            return layout;
        },

        displayError: function (msg) {
            msg = msg || 'error';

            this.$el.find('.report-results-container')
                .html(this.translate(msg, 'errorMessages', 'Report'));
        },

        displayTotal: function (dataList, isWithChart) {
            let fontSize = this.getThemeManager().getParam('fontSize') || 14;
            let cellWidth = 100 / dataList.length;

            let labelFontSize;

            if (!isWithChart) {
                this.$container.empty();

                let totalFontSize = fontSize * this.totalOnlyFontSizeMultiplier;

                if (dataList.length > 1) {
                    totalFontSize = Math.round(totalFontSize / (Math.log(dataList.length + 1) / Math.log(2.3)));
                    labelFontSize = Math.round(totalFontSize * this.total2LabelMultiplier);
                }

                this.$container.css('height', '100%');

                let $div = $('<div>')
                    .css('text-align', 'center')
                    .css('table-layout', 'fixed')
                    .css('display', 'table')
                    .css('width', '100%')
                    .css('height', '100%');

                dataList.forEach(item => {
                    let value = item.stringValue;
                    let color = item.color;

                    let $cell = $('<div>')
                        .css('display', 'table-cell')
                        .css('padding-bottom', fontSize * 1.5 + 'px')
                        .css('vertical-align', 'middle');

                    if (cellWidth < 100) {
                        $cell.css('width', cellWidth.toString() + '%');
                    }

                    let $text = $('<div class="total-value-text">')
                        .css('font-size', totalFontSize + 'px')
                        .html(value.toString());

                    if (item.stringOriginalValue) {
                        $text.attr('title', item.stringOriginalValue);
                    }

                    if (color) {
                        $text.css('color', color);
                    } else {
                        if (!isWithChart) {
                            $text.addClass('text-primary');
                        }
                    }

                    if (dataList.length > 1) {
                        let $label = $('<div>')
                            .css('font-size', labelFontSize.toString() + 'px')
                            .css('max-height', '1.3em')
                            .css('overflow', 'hidden')
                            .addClass('text-muted')
                            .html(item.columnLabel);

                        $cell.append($label);
                    }

                    $cell.append($text);
                    $div.append($cell);
                });

                this.$container.append($div);


                this.totalFontSize = totalFontSize;
                this.controlTotalTextOverflow();

                this.stopListening(this, 'resize', this.controlTotalTextOverflow.bind(this));
                this.listenTo(this, 'resize', this.controlTotalTextOverflow.bind(this));

                return;
            }

            let totalFontSize = fontSize * this.totalFontSizeMultiplier;

            if (dataList.length > 1) {
                labelFontSize = Math.round(totalFontSize * this.totalLabelMultiplier);
            }

            let heightCss = this.getContainerTotalHeight(dataList.length > 1) + 'px';

            let $div = $('<div>')
                .css('text-align', 'center')
                .css('display', 'table')
                .css('width', '100%');

            this.$totalContainer.css('height', heightCss);
            this.$container.css('height', 'calc(100% - '+heightCss+')');

            dataList.forEach(item => {
                let value = item.stringValue;

                let $text = $('<div>').html(value.toString());

                let title = '';

                if (item.stringOriginalValue) {
                    title = item.stringOriginalValue;
                }

                if (dataList.length === 1) {
                    $text.addClass('pull-right');

                    let totalPart = title;

                    title = this.translate('Total', 'labels', 'Report');

                    if (totalPart) {
                        title = title + ': ' + totalPart;
                    }
                }

                $text
                    .attr('title', title)
                    .addClass('text-primary')
                    .css('font-size', Math.ceil(totalFontSize) + 'px');

                if (dataList.length === 1) {
                    $text.css('line-height', heightCss);
                }

                let $cell = $('<div>')
                    .css('display', 'table-cell');

                if (cellWidth < 100) {
                    $cell.css('width', cellWidth.toString() + '%');
                }

                if (dataList.length > 1) {
                    let $label = $('<div>')
                        .css('font-size', labelFontSize.toString() + 'px')
                        .css('max-height', '1.2em')
                        .css('overflow', 'hidden')
                        .addClass('text-muted')
                        .html(item.columnLabel);

                    $cell.append($label);
                }

                $cell.append($text);
                $div.append($cell);
            });

            this.$totalContainer.append($div);
        },

        controlTotalTextOverflow: function () {
            let totalFontSizeAdj = this.totalFontSize;

            let $text = this.$el.find('.total-value-text');

            $text.css('font-size', totalFontSizeAdj + 'px');

            let controlOverflow = () => {
                let isOverflown = false;

                $text.each((i, el) => {
                    if (el.scrollWidth > el.clientWidth) {
                        isOverflown = true;
                    }
                });

                if (isOverflown) {
                    totalFontSizeAdj--;
                    $text.css('font-size', totalFontSizeAdj + 'px');
                    controlOverflow();
                }
            };

            controlOverflow();
        },

        getContainerTotalHeight: function (withLabels) {
            let fontSize = this.getThemeManager().getParam('fontSize') || 14;

            let totalFontSize = fontSize * this.totalFontSizeMultiplier;
            let totalPadding = fontSize * this.totalMarginMultiplier;

            let height = Math.ceil(totalFontSize * this.totalLineHeightMultiplier + totalPadding);

            if (withLabels) {
                height = height + height * this.totalLabelMultiplier;
            }

            return height;
        },

        actionRefresh: function () {
            if (this.hasView('reportChart')) {
                this.clearView('reportChart');
            }

            this.reRender();
        },

        afterRender: function () {
            this.$container = this.$el.find('.report-results-container');
            this.run();
        },

        getCollectionUrl: function () {
            return 'Report/action/runList?id=' + this.getOption('reportId');
        },

        getGridReportUrl: function () {
            return 'Report/action/run';
        },

        getGridReportRequestData: function (where) {
            return {
                id: this.getOption('reportId'),
                where: where,
            }
        },

        setContainerHeight: function () {
            let type = this.getOption('type');

            if (type === 'List') {
                this.$container.css('height', 'auto');
            } else {
                this.$container.css('height', '100%');
            }
        },

        run: function () {
            let reportId = this.getOption('reportId');

            if (!reportId) {
                this.displayError('selectReport');
                return;
            }

            let entityType = this.getOption('entityType');

            if (!entityType) {
                this.displayError();

                return;
            }

            let type = this.getOption('type');

            if (!type) {
                this.displayError();

                return;
            }

            this.setContainerHeight();

            this.getCollectionFactory().create(entityType, collection => {
                let searchManager = new SearchManager(collection, 'report', null, this.getDateTime());
                let where = null;

                if (this.getOption('filtersData')) {
                    searchManager.setAdvanced(this.getOption('filtersData'));
                    where = searchManager.getWhere();
                }

                switch (type) {
                    case 'List':
                        collection.url = this.getCollectionUrl();
                        collection.where = where;

                        if (collection.setOrder) {
                            collection.setOrder(null, null, true);
                        }

                        if (this.collectionMaxSize) {
                            collection.maxSize = this.collectionMaxSize;
                        }

                        let collectionData = {
                            where: collection.getWhere(),
                            offset: collection.offset,
                            maxSize: collection.maxSize,
                        };

                        Espo.Ajax.getRequest(collection.url, collectionData).then(response => {
                            let columns = this.columns = response.columns;

                            this.columnsData = response.columnsData || {};

                            let attributes = collection.prepareAttributes ?
                                collection.prepareAttributes(response) :
                                collection.parse(response);

                            collection.set(attributes);

                            if (!columns) {
                                this.displayError();

                                return;
                            }

                            if (this.getOption('displayOnlyCount')) {
                                let totalString = this.reportHelper.formatNumber(
                                    collection.total,
                                    false,
                                    this.getOption('useSiMultiplier')
                                );

                                let o = {stringValue: totalString};

                                if (this.getOption('useSiMultiplier')) {
                                    o.stringOriginalValue = this.reportHelper.formatNumber(collection.total, false);
                                }

                                this.displayTotal([o]);

                                return;
                            }

                            this.createView('list', 'views/record/list', {
                                el: this.options.el + ' .report-results-container',
                                collection: collection,
                                listLayout: this.getListLayout(),
                                checkboxes: false,
                                rowActionsView: this.rowActionsView,
                                displayTotalCount: false,
                            }, view => {
                                view.render();
                            });
                        });

                        break;

                    case 'Grid':
                    case 'JointGrid':
                        Espo.Ajax.getRequest(this.getGridReportUrl(), this.getGridReportRequestData(where))
                        .then(result => {
                            if (!result.depth && result.depth !== 0) {
                                this.displayError();

                                return;
                            }

                            let chartType = result.chartType || 'BarHorizontal';

                            let height;
                            let fitHeight = false;

                            if (!this.isPanel) {
                                height = '100%';

                                if (result.depth === 2 || ~['Pie'].indexOf(chartType)) {
                                    fitHeight = true;
                                }
                            }

                            let column = this.getOption('column');
                            let columnList, secondColumnList;

                            if (!column) {
                                let columnGroupList = this.reportHelper.getChartColumnGroupList(result);

                                if (columnGroupList.length) {
                                    columnList = columnGroupList[0].columnList;
                                    secondColumnList = columnGroupList[0].secondColumnList;
                                    column = columnGroupList[0].column;

                                    if (!column) {
                                        if (!this.isPanel) {
                                            fitHeight = true;
                                        }
                                    }
                                }
                            }

                            let totalColumnList = result.numericColumnList || result.columnList;

                            let totalDataList = [];

                            if (this.getOption('displayType') === 'Table') {
                                this.displayTable(result, where);

                                return;
                            }

                            if (
                                totalColumnList.length &&
                                (this.getOption('displayOnlyCount') || this.getOption('displayTotal'))
                            ) {
                                totalColumnList.forEach(totalColumn => {
                                    let total;

                                    if (result.depth === 1 || result.depth === 0) {
                                        total = result.sums[totalColumn] || 0;
                                    }
                                    else {
                                        total = 0;

                                        for (let i in result.group1Sums) {
                                            total += result.group1Sums[i][totalColumn];
                                        }
                                    }

                                    let totalString = this.reportHelper.formatCellValue(
                                        total,
                                        totalColumn,
                                        result,
                                        this.getOption('useSiMultiplier')
                                    );

                                    let totalColor = result.chartColor;

                                    if ((result.chartColors || {})[totalColumn]) {
                                        totalColor = (result.chartColors || {})[totalColumn]
                                    }

                                    if (!result.chartType) {
                                        totalColor = null;
                                    }

                                    let stringOriginalValue = null;

                                    if (this.getOption('useSiMultiplier')) {
                                            stringOriginalValue = this.reportHelper.formatCellValue(
                                            total,
                                            totalColumn,
                                            result
                                        );
                                    }

                                    totalDataList.push({
                                        column: totalColumn,
                                        color: totalColor,
                                        stringValue: totalString,
                                        columnLabel: this.reportHelper.formatColumn(totalColumn, result),
                                        stringOriginalValue: stringOriginalValue,
                                    });
                                });
                            }

                            if (totalColumnList.length && this.getOption('displayOnlyCount')) {
                                this.displayTotal(totalDataList);

                                return;
                            }

                            if (totalColumnList.length && this.getOption('displayTotal')) {
                                this.$totalContainer = $('<div class="report-total-container"></div>');
                                this.$totalContainer.insertBefore(this.$container);

                                this.displayTotal(totalDataList, true);
                            }

                            this.$el.closest('.panel-body').css({
                                'overflow-y': 'visible',
                                'overflow-x': 'visible',
                            });

                            this.createView('reportChart',
                                'advanced:views/report/reports/charts/grid' +
                                result.depth + Espo.Utils.camelCaseToHyphen(chartType),
                            {
                                el: this.options.el + ' .report-results-container',
                                column: column,
                                columnList: columnList,
                                secondColumnList: secondColumnList,
                                result: result,
                                reportHelper: this.reportHelper,
                                height: height,
                                fitHeight: fitHeight,
                                colors: result.chartColors || {},
                                color: result.chartColor || null,
                                defaultHeight: this.defaultHeight,
                                isDashletMode: true,
                            }, view => {
                                if (!this._isHidden()) {
                                    view.render();
                                }
                                else {
                                    this.once('show', () => {
                                        if (!this._isHidden()) {
                                            view.render();
                                        }
                                    });

                                    this.once('tab-show', () => {
                                        if (!this._isHidden()) {
                                            view.render();
                                        }
                                    });
                                }

                                this.on('resize', () => {
                                    view.trigger('resize')
                                });

                                this.listenTo(view, 'click-group', (groupValue, groupIndex, groupValue2, column) => {
                                    this.showSubReport(
                                        where,
                                        result,
                                        groupValue,
                                        groupIndex,
                                        groupValue2,
                                        column
                                    );
                                });
                            });
                        });

                        break;
                }
            });
        },

        _isHidden: function () {
            return false;
        },

        showSubReport: function (where, result, groupValue, groupIndex, groupValue2, column) {
            let reportId = this.getOption('reportId');
            let entityType = this.getOption('entityType');

            if (result.isJoint) {
                reportId = result.columnReportIdMap[column];
                entityType = result.columnEntityTypeMap[column];
            }

            this.getCollectionFactory().create(entityType, collection => {
                collection.url = 'Report/action/runList?id=' + reportId +
                    '&groupValue=' + encodeURIComponent(groupValue);

                if (groupIndex) {
                    collection.url += '&groupIndex=' + groupIndex;
                }

                if (groupValue2 !== undefined) {
                    collection.url += '&groupValue2=' + encodeURIComponent(groupValue2);
                }

                if (where) {
                    collection.where = where;
                }

                collection.maxSize = this.getConfig().get('recordsPerPage');

                Espo.Ui.notify(' ... ');

                this.createView('subReport', 'advanced:views/report/modals/sub-report', {
                    reportId: this.getOption('reportId'),
                    reportName: this.getOption('title'),
                    result: result,
                    groupValue: groupValue,
                    groupIndex: groupIndex,
                    groupValue2: groupValue2,
                    collection: collection,
                    column: column,
                }, view => {
                    Espo.Ui.notify(false);

                    view.render();
                });
            });
        },

        setupActionList: function () {
            this.actionList.unshift({
                'name': 'viewReport',
                'html': this.translate('View Report', 'labels', 'Report'),
                'url': '#Report/show/' + this.getOption('reportId'),
                iconHtml: '<span class="fas fa-chart-bar"></span>',
            });
        },

        displayTable: function (result, where) {
            let viewName = 'advanced:views/report/reports/tables/grid1';

            if (result.depth === 2) {
                viewName = 'advanced:views/report/reports/tables/grid2';
            }

            this.createView('table', viewName, {
                el: this.options.el + ' .report-results-container',
                result: result,
                reportHelper: this.reportHelper,
                column: this.getOption('column'),
            }).then(view => {
                view.render();

                this.listenTo(view, 'click-group', (groupValue, groupIndex) => {
                    this.showSubReport(where,
                        result,
                        groupValue,
                        groupIndex,
                        undefined,
                        this.getOption('column')
                    );
                });
            });
        },

        actionViewReport: function () {
            let reportId = this.getOption('reportId');

            Espo.Ui.notify(' ... ');

            this.getModelFactory().create('Report', model => {
                model.id = reportId;

                model.fetch().then(() => {
                    Espo.Ui.notify(false);

                    this.createView('resultModal', 'advanced:views/report/modals/result', {model: model}, view => {
                        view.render();

                        this.listenToOnce(view, 'navigate-to-detail', model => {
                            this.getRouter().navigate('#Report/view/' + model.id, {trigger: false});
                            this.getRouter().dispatch('Report', 'view', {id: model.id, model: model});

                            view.close();
                        });
                    });
                });
            });
        },
    });
});
