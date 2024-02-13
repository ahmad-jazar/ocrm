
define('advanced:views/workflow/action-fields/subjects/field', ['view'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/action-fields/subjects/field',

        data: function () {
            return {
                value: this.options.value,
                entityType: this.options.entityType,
                listHtml: this.listHtml,
                readOnly: this.readOnly
            };
        },

        setup: function () {
            var entityType = this.options.entityType;
            var scope = this.options.scope;
            var field = this.options.field;
            this.readOnly = this.options.readOnly;

            var foreignScope;

            var value = this.options.value;

            var fieldType = this.getMetadata().get('entityDefs.' + scope + '.fields.' + field + '.type') || 'base';

            var fieldTypeList = this.getMetadata().get('entityDefs.Workflow.fieldTypeComparison.' + fieldType) || [];

            if (fieldType === 'link' || fieldType === 'linkMultiple') {
                foreignScope = this.getMetadata().get('entityDefs.' + scope + '.links.' + field + '.entity');
            }

            if (this.readOnly) {
                if (~value.indexOf('.')) {
                    var values = value.split(".");
                    this.listHtml = this.translate('Field', 'labels', 'Workflow') + ': ' +
                        this.translate(values[0], 'links', entityType) + '.' +
                        this.translate(values[1], 'fields', foreignScope);
                } else {
                    this.listHtml = this.translate('Field', 'labels', 'Workflow') + ': ' +
                        this.translate(value, 'fields', entityType);
                }

                return;
            }

            var list = [];
            var fieldDefs = this.getMetadata().get('entityDefs.' + entityType + '.fields');

            Object.keys(fieldDefs).forEach(function (f) {
                if ((fieldDefs[f].type === fieldType || ~fieldTypeList.indexOf(fieldDefs[f].type))) {
                    if (fieldDefs[f].directAccessDisabled) {
                        return;
                    }

                    if (
                        fieldDefs[f].disabled ||
                        fieldDefs[f].utility
                    ) {
                        return;
                    }

                    if (fieldType === 'link' || fieldType === 'linkMultiple') {
                        var fScope = this.getMetadata().get('entityDefs.' + entityType + '.links.' + f + '.entity');

                        if (fScope !== foreignScope) {
                            return;
                        }
                    }
                    list.push(f);
                }
            }, this);

            var listHtml = '';

            list.forEach(function (f, i) {
                if (i == 0) {
                    var label = this.translate('Target Entity', 'labels', 'Workflow') +
                        ' (' + this.translate(entityType, 'scopeNames') + ')';
                    listHtml += '<optgroup label="' + label + '">';
                }

                var selectedHtml = '';

                if (value == f) {
                    selectedHtml = 'selected';
                }

                listHtml += '<option ' + selectedHtml + ' value="' + f + '">' +
                    this.translate(f, 'fields', entityType) + '</option>';
                if (i == list.length - 1) {
                    listHtml += '</optgroup>';
                }
            }, this);

            var relatedFields = {};

            var linkDefs = this.getMetadata().get('entityDefs.' + entityType + '.links');

            Object.keys(linkDefs).forEach(function (link) {
                var list = [];

                if (linkDefs[link].type === 'belongsTo') {
                    if (
                        linkDefs[link].disabled ||
                        linkDefs[link].utility
                    ) {
                        return;
                    }

                    var foreignEntityType = linkDefs[link].entity;

                    if (!foreignEntityType) {
                        return;
                    }

                    var fieldDefs = this.getMetadata().get('entityDefs.' + foreignEntityType + '.fields');

                    Object.keys(fieldDefs).forEach(function (f) {
                        if (fieldDefs[f].type === fieldType || ~fieldTypeList.indexOf(fieldDefs[f].type)) {
                            if (fieldDefs[f].directAccessDisabled) {
                                return;
                            }

                            if (
                                fieldDefs[f].disabled ||
                                fieldDefs[f].utility
                            ) {
                                return;
                            }

                            if (fieldType === 'link' || fieldType === 'linkMultiple') {
                                var fScope = this.getMetadata()
                                    .get('entityDefs.' + foreignEntityType + '.links.' + f + '.entity');

                                if (fScope !== foreignScope) {
                                    return;
                                }
                            }

                            list.push(f);
                        }
                    }, this);

                    relatedFields[link] = list;
                }
            }, this);

            for (var link in relatedFields) {
                relatedFields[link].forEach(function (f, i) {
                    if (i === 0) {
                        listHtml += '<optgroup label="' + this.translate(link, 'links', entityType) + '">';
                    }

                    var selectedHtml = false;
                    if (value === link + '.' + f) {
                        selectedHtml = 'selected';
                    }

                    listHtml += '<option ' + selectedHtml + ' value="' + link + '.' + f + '">' +
                        this.translate(link, 'links', entityType) + '.' +
                        this.translate(f, 'fields', linkDefs[link].entity) + '</option>';

                    if (i === relatedFields[link].length - 1) {
                        listHtml += '</optgroup>';
                    }
                }, this);
            }

            this.listHtml = listHtml;
        },
    });
});
