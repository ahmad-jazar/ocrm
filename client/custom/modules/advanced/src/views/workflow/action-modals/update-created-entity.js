
define('advanced:views/workflow/action-modals/update-created-entity',
['advanced:views/workflow/action-modals/create-entity', 'model'], function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow/action-modals/update-created-entity',

        data: function () {
            return _.extend({
                target: this.actionData.target,
                scope: this.scope,
            });
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            var model = new Model();
            model.name = 'Workflow';

            this.modelForParentEntityType = model;

            this.actionModel = model;

            var targetList = Object.keys(this.options.flowchartCreatedEntitiesData).map(item => {
                return 'created:' + item;
            });

            targetList = Espo.Utils.clone(targetList);
            targetList.unshift('');

            if (this.actionData.target) {
                model.set('target', this.actionData.target);
            }

            var translatedOptions = {};
            translatedOptions[''] = '--' + this.translate('Select') + '--';

            Object.keys(this.options.flowchartCreatedEntitiesData).forEach(aliasId => {
                var link = this.options.flowchartCreatedEntitiesData[aliasId].link;
                var entityType = this.options.flowchartCreatedEntitiesData[aliasId].entityType;
                var numberId = this.options.flowchartCreatedEntitiesData[aliasId].numberId;
                var text = this.options.flowchartCreatedEntitiesData[aliasId].text;

                var label = this.translate('Created', 'labels', 'Workflow') + ': ';

                if (link) {
                    label += this.translate(link, 'links', this.entityType) + ' - ';
                }

                label += this.translate(entityType, 'scopeNames');

                if (text) {
                    label += ' \'' + text + '\'';
                } else {
                    if (numberId) {
                        label += ' #' + numberId.toString();
                    }
                }

                translatedOptions['created:' + aliasId] = label;
            });

            this.createView('target', 'views/fields/enum', {
                mode: 'edit',
                model: model,
                el: this.getSelector() + ' .field[data-name="target"]',
                defs: {
                    name: 'target',
                    params: {
                        options: targetList
                    }
                },
                translatedOptions: translatedOptions,
                readOnly: this.readOnly,
            });

            this.listenTo(model, 'change:target', () => {
                this.setTarget(this.actionModel.get('target'));
            });
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
        },

        setupScope: function (callback) {
            if (this.actionData.target) {

                var scope = this.scope = null;

                var aliasId = this.actionData.target.substr(8);

                if (!this.options.flowchartCreatedEntitiesData[aliasId]) {
                    callback();
                    return;
                }

                scope = this.options.flowchartCreatedEntitiesData[aliasId].entityType;

                this.scope = scope;

                if (scope) {
                    this.wait(true);

                    this.getModelFactory().create(scope, model => {
                        this.model = model;

                        (this.actionData.fieldList || []).forEach(field => {
                            var attributes = (this.actionData.fields[field] || {}).attributes || {};

                            model.set(attributes, {silent: true});
                        });

                        callback();
                    });
                }
                else {
                    throw new Error;
                }
            }
            else {
                this.model = null;
                callback();
            }
        },

        setTarget: function (value) {
            this.actionData.target = value;

            this.actionData.fieldList.forEach(field => {
                this.$el.find('.field-row[data-field="' + field + '"]').remove();

                this.clearView('field-' + field);
            });

            this.actionData.fieldList = [];
            this.actionData.fields = {};

            this.handleLink();
        },

        handleLink: function () {
            var target = this.actionData.target;

            if (!target) {
                this.clearView('addField');
                this.clearView('formula');
                this.$formulaCell.addClass('hidden');

                return;
            }

            if (this.hasFormulaAvailable) {
                this.$formulaCell.removeClass('hidden');
            }

            this.setupScope(() => {
                this.createView('addField', 'advanced:views/workflow/action-fields/add-field', {
                    el: this.options.el + ' .add-field-container',
                    scope: this.scope,
                    fieldList: this.getFieldList(),
                }, view => {
                    view.render();
                });
            });

            this.setupFormulaView();
        },

        setupFormulaView: function () {
            var model = new Model;

            if (this.hasFormulaAvailable) {
                model.set('formula', this.actionData.formula || null);

                this.createView('formula', 'views/fields/formula', {
                    name: 'formula',
                    model: model,
                    mode: this.readOnly ? 'detail' : 'edit',
                    height: 100,
                    el: this.getSelector() + ' .field[data-name="formula"]',
                    inlineEditDisabled: true,
                    targetEntityType: this.scope,
                }, view => {
                    view.render();
                });
            }
        },

        fetch: function () {
            var isValid = true;

            (this.actionData.fieldList || []).forEach(field => {
                isValid = this.getView('field-' + field).fetch();

                this.actionData.fields[field] = this.getView('field-' + field).fieldData;
            });

            if (this.hasFormulaAvailable) {
                if (this.actionData.target) {
                    var formulaView = this.getView('formula');

                    if (formulaView) {
                        this.actionData.formula = formulaView.fetch().formula;
                    }
                }
            }

            return isValid;
        },
    });
});
