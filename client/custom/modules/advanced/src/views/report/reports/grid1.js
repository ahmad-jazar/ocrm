
define('advanced:views/report/reports/grid1', ['advanced:views/report/reports/base'], function (Dep) {

    return Dep.extend({

        setup: function () {
            this.initReport();
        },

        export: function () {
            let where = this.getRuntimeFilters();

            let o = {
                scope: this.model.get('entityType'),
                reportType: 'Grid',
            };

            let url;

            let data = {
                id: this.model.id,
                where: where,
            };

            this.createView('dialogExport', 'advanced:views/report/modals/export-grid', o, view => {
                view.render();

                this.listenToOnce(view, 'proceed', (dialogData) => {
                    data.where = where;

                    if (dialogData.format === 'csv') {
                        url = 'Report/action/exportGridCsv';
                    } else if (dialogData.format === 'xlsx') {
                        url = 'Report/action/exportGridXlsx';
                    }

                    Espo.Ui.notify(' ... ');

                    Espo.Ajax.postRequest(url, data, {timeout: 0}).then(response => {
                        Espo.Ui.notify(false);

                        if ('id' in response) {
                            window.location = this.getBasePath() + '?entryPoint=download&id=' + response.id;
                        }
                    });
                });
            });
        },

        run: function () {
            Espo.Ui.notify(' ... ');

            let $container = this.$el.find('.report-results-container');
            $container.empty();

            let where = this.getRuntimeFilters();

            Espo.Ajax.getRequest('Report/action/run', {
                id: this.model.id,
                where: where,
            }, {timeout: 0}).then(result => {
                Espo.Ui.notify(false);

                this.result = result;

                this.storeRuntimeFilters();

                let $tableContainer = $('<div>').addClass('report-table').addClass('section');

                if (!this.options.showChartFirst) {
                    $container.append($tableContainer);
                }

                let columnGroupList;

                if (this.chartType) {
                    let headerTag = this.options.isLargeMode ? 'h4' : 'h5';
                    let headerMarginTop = this.options.isLargeMode ? 60 : 0;

                    columnGroupList = this.options.reportHelper.getChartColumnGroupList(result);

                    columnGroupList.forEach((item, i) => {
                        let column = item.column;

                        let $column = $('<div>')
                            .addClass('section')
                            .addClass('column-' + i);

                        if (column) {
                            let $header = $('<'+headerTag+' style="margin-bottom: 25px">' +
                                this.options.reportHelper.formatColumn(column, result) + '</'+headerTag+'>');

                            if (headerMarginTop && i) {
                                $header.css('marginTop', headerMarginTop);
                            }

                            $column.append($header);
                        }

                        let $chartContainer = $('<div>')
                            .addClass('section')
                            .addClass('report-chart')
                            .addClass('report-chart-' + i);

                        $column.append($chartContainer);
                        $container.append($column);
                    });
                }

                if (this.options.showChartFirst) {
                    $container.append($tableContainer);
                }

                this.createView('reportTable', 'advanced:views/report/reports/tables/grid1', {
                    el: this.options.el + ' .report-results-container .report-table',
                    result: result,
                    reportHelper: this.options.reportHelper,
                    hasChart: !!this.chartType,
                    isLargeMode: this.options.isLargeMode,
                }, (view) => {
                    view.render();
                });

                if (this.chartType) {
                    columnGroupList.forEach((item, i) => {
                        let column = item.column;
                        let columnList = item.columnList;
                        let secondColumnList = item.secondColumnList;

                        let viewName = 'advanced:views/report/reports/charts/grid1' +
                            Espo.Utils.camelCaseToHyphen(this.chartType);

                        this.createView('reportChart' + i, viewName, {
                            el: this.options.el + ' .report-results-container .column-' + i + ' .report-chart',
                            column: column,
                            columnList: columnList,
                            secondColumnList: secondColumnList,
                            result: result,
                            reportHelper: this.options.reportHelper,
                            colors: result.chartColors || {},
                            color: result.chartColor || null,
                        }, (view) => {
                            view.render();

                            this.listenTo(view, 'click-group', (groupValue, s1, s2, column) => {
                                this.showSubReport(groupValue, undefined, undefined, column);
                            });
                        });
                    });
                }
            });
        },

        getPDF: function (id, where) {
            this.getRouter();
        },
    });
});
