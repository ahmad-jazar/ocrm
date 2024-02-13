
define('advanced:views/workflow/record/conditions', ['view'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/record/conditions',

        ignoreFieldList: [],

        events: {
            'click [data-action="showAddCondition"]': function (e) {
                let $target = $(e.currentTarget);
                let conditionType = $target.data('type');

                this.createView('modal', 'advanced:views/workflow/modals/add-condition', {
                    scope: this.entityType,
                    createdEntitiesData: this.options.flowchartCreatedEntitiesData,
                }, view => {
                    view.render();

                    this.listenToOnce(view, 'add-field', field => {
                        this.clearView('modal');
                        this.addCondition(conditionType, field, {}, true);
                    });
                });
            },
            'click [data-action="removeCondition"]': function (e) {
                let $target = $(e.currentTarget);
                let id = $target.data('id');

                this.clearView('condition-' + id);

                let $conditionContainer = $target.parent();
                let $container = $conditionContainer.parent();

                $conditionContainer.remove();

                if (!$container.find('.condition').length) {
                    $container.find('.no-data').removeClass('hidden');
                }

                this.trigger('change');
            }
        },

        data: function () {
            let hasConditionsAll = !!(this.model.get('conditionsAll') || []).length;
            let hasConditionsAny = !!(this.model.get('conditionsAny') || []).length;
            let hasConditionsFormula = !!(this.model.get('conditionsFormula') || '');

            return {
                fieldList: this.fieldList,
                entityType: this.entityType,
                readOnly: this.readOnly,
                showFormula: !this.readOnly || hasConditionsFormula,
                showConditionsAny: !this.readOnly || hasConditionsAny,
                showConditionsAll: !this.readOnly || hasConditionsAll,
                showNoData: this.readOnly && !hasConditionsFormula && !hasConditionsAny && !hasConditionsAll,
                marginForConditionsAny: !this.readOnly || hasConditionsAll,
                marginForFormula: !this.readOnly || hasConditionsAll || hasConditionsAny,
            }
        },

        afterRender: function () {
            let conditionsAll = this.model.get('conditionsAll') || [];
            let conditionsAny = this.model.get('conditionsAny') || [];

            conditionsAll.forEach(data => {
                this.addCondition('all', data.fieldToCompare, data);
            });

            conditionsAny.forEach(data => {
                this.addCondition('any', data.fieldToCompare, data);
            });

            if (!this.readOnly || this.model.get('conditionsFormula')) {
                this.createView('conditionsFormula', 'views/fields/formula', {
                    name: 'conditionsFormula',
                    model: this.model,
                    mode: this.readOnly ? 'detail' : 'edit',
                    height: 50,
                    el: this.getSelector() + ' .formula-conditions',
                    inlineEditDisabled: true,
                    targetEntityType: this.entityType,
                }, view => {
                    view.render();

                    this.listenTo(view, 'change', () => {
                        this.trigger('change');
                    });
                });
            }
        },

        setup: function () {
            this.entityType = this.scope = this.options.entityType || this.model.get('entityType');

            let conditionFieldTypes = this.getMetadata().get('entityDefs.Workflow.conditionFieldTypes') || {};
            let defs = this.getMetadata().get('entityDefs.' + this.entityType + '.fields');

            this.fieldList = Object.keys(defs)
                .filter(field => {
                    let type = defs[field].type || 'base';

                    if (
                        defs[field].disabled ||
                        defs[field].utility
                    ) {
                        return;
                    }

                    return !~this.ignoreFieldList.indexOf(field) && (type in conditionFieldTypes);
                })
                .sort((v1, v2) => {
                     return this.translate(v1, 'fields', this.scope)
                         .localeCompare(this.translate(v2, 'fields', this.scope));
                });

            this.lastCid = 0;
            this.readOnly = this.options.readOnly || false;
        },

        addCondition: function (conditionType, field, data, isNew) {
            data = data || {};

            let numberId;
            let fieldType;
            let link = null;
            let foreignField = null;
            let isCreatedEntity = false;

            let overriddenEntityType = null;
            let overriddenField;
            let foreignEntityType;
            let aliasId;

            if (~field.indexOf('.')) {
                if (field.indexOf('created:') === 0) {
                    isCreatedEntity = true;

                    let arr = field.split('.');
                    overriddenField = arr[1];

                    aliasId = arr[0].substr(8);

                    if (
                        !this.options.flowchartCreatedEntitiesData ||
                        !this.options.flowchartCreatedEntitiesData[aliasId]
                    ) {
                        return;
                    }

                    overriddenEntityType = this.options.flowchartCreatedEntitiesData[aliasId].entityType;
                    link = this.options.flowchartCreatedEntitiesData[aliasId].link;
                    numberId = this.options.flowchartCreatedEntitiesData[aliasId].numberId;

                    fieldType = this.getMetadata()
                        .get(['entityDefs', overriddenEntityType, 'fields', overriddenField, 'type']) || 'base';
                }
                else {
                    let arr = field.split('.');

                    foreignField = arr[1];
                    link = arr[0];

                    foreignEntityType = this.getMetadata()
                        .get(['entityDefs', this.entityType, 'links', link, 'entity']);

                    fieldType = this.getMetadata()
                        .get(['entityDefs', foreignEntityType, 'fields', foreignField, 'type']) || 'base';
                }
            }
            else {
                fieldType = this.getMetadata()
                    .get(['entityDefs', this.entityType, 'fields', field, 'type']) || 'base';
            }

            let type = this.getMetadata()
                .get('entityDefs.Workflow.conditionFieldTypes.' + fieldType) || 'base';

            let $container = this.$el.find('.' + conditionType.toLowerCase() + '-conditions');

            $container.find('.no-data').addClass('hidden');

            let id = data.cid  = this.lastCid;

            this.lastCid++;

            let label;

            let actualField = field;
            let actualEntityType = this.entityType;

            if (isCreatedEntity) {
                let labelLeftPart = this.translate('Created', 'labels', 'Workflow') + ': ';

                if (link) {
                    labelLeftPart += this.translate(link, 'links', this.entityType) + ' - ';
                }

                labelLeftPart += this.translate(overriddenEntityType, 'scopeNames');

                let text = this.options.flowchartCreatedEntitiesData[aliasId].text;

                if (text) {
                    labelLeftPart += ' \'' + text + '\'';
                } else {
                    if (numberId) {
                        labelLeftPart += ' #' + numberId.toString();
                    }
                }

                label = labelLeftPart + '.' + this.translate(overriddenField, 'fields', overriddenEntityType);

                actualField = overriddenField;
                actualEntityType = overriddenEntityType;
            }
            else if (link) {
                label = this.translate(link, 'links', this.entityType) + '.' +
                    this.translate(foreignField, 'fields', foreignEntityType);

                actualField = foreignField;
                actualEntityType = foreignEntityType;
            }
            else {
                label = this.translate(field, 'fields', this.entityType);
            }

            let escapedId = this.getHelper().escapeString(id);
            let escapedLabel = this.getHelper().escapeString(label);

            let fieldNameHtml = '<label class="field-label-name control-label small">'
                + escapedLabel + '</label>';

            let removeLinkHtml = this.readOnly ? '' :
                '<a role="button" tabindex="0" class="pull-right" data-action="removeCondition" ' +
                'data-id="' + escapedId + '">' +
                '<span class="fas fa-times"></span></a>';

            let html = '<div class="cell form-group" style="margin-left: 20px;">' +
                removeLinkHtml + fieldNameHtml +
                '<div class="condition small" data-id="' + escapedId + '"></div></div>';

            $container.append(html);

            let viewName = 'advanced:views/workflow/conditions/' + Espo.Utils.camelCaseToHyphen(type);

            this.createView('condition-' + id, viewName, {
                el: this.options.el + ' .condition[data-id="' + id + '"]',
                conditionData: data,
                model: this.model,
                field: field,
                entityType: overriddenEntityType || this.entityType,
                originalEntityType: this.entityType,
                actualField: actualField,
                actualEntityType: actualEntityType,
                type: type,
                fieldType: fieldType,
                conditionType: conditionType,
                isNew: isNew,
                readOnly: this.readOnly,
                isChangedDisabled: this.options.isChangedDisabled,
            }, view => {
                view.render();

                if (isNew) {
                    let $form = view.$el.closest('.form-group');

                    $form.addClass('has-error');

                    setTimeout(() => $form.removeClass('has-error'), 1500);

                    this.trigger('change');
                }
            });
        },

        fetch: function () {
            if (!this.hasView('conditionsFormula')) {
                // Prevents fetching on duplicate action, when fields are not yet rendered (in afterRender).
                return null;
            }

            let conditions = {
                all: [],
                any: [],
            };

            for (let i = 0; i < this.lastCid; i++) {
                let view = this.getView('condition-' + i);

                if (!view) {
                    continue;
                }

                if (!(view.conditionType in conditions)) {
                    continue;
                }

                let data = view.fetch();

                data.type = view.conditionType;
                conditions[view.conditionType].push(data);
            }

            let view = this.getView('conditionsFormula');

            if (view) {
                conditions.formula = (view.fetch() || {}).conditionsFormula;
            }

            return conditions;
        },
    });
});
