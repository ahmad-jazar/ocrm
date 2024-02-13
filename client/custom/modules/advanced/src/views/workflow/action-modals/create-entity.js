
define('advanced:views/workflow/action-modals/create-entity', ['advanced:views/workflow/action-modals/base', 'model'],
function (Dep, Model) {

    return Dep.extend({

        template: 'advanced:workflow/action-modals/create-entity',

        data: function () {
            return _.extend({
                link: this.actionData.link,
                linkOptions: this.getLinkOptionsHtml(),
                scope: this.scope,
            }, Dep.prototype.data.call(this));
        },

        events: {
            'change [name="link"]': function (e) {
                this.changeLinkAction(e);
            },
            'click [data-action="addField"]': function (e) {
                var $target = $(e.currentTarget);
                var field = $target.data('field');

                if (!~this.actionData.fieldList.indexOf(field)) {

                    this.actionData.fieldList.push(field);
                    this.actionData.fields[field] = {};

                    this.addField(field, false, true);
                }
            },
            'click [data-action="removeField"]': function (e) {
                var $target = $(e.currentTarget);
                var field = $target.data('field');
                this.clearView('field-' + field);

                delete this.actionData.fields[field];

                var index = this.actionData.fieldList.indexOf(field);
                this.actionData.fieldList.splice(index, 1);

                $target.parent().remove();
            },
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            this.$fieldDefinitions = this.$el.find('.field-definitions');
            this.$formulaCell = this.$el.find('.cell[data-name="formula"]');

            this.handleLink();

            (this.actionData.fieldList || []).forEach(field => {
                this.addField(field, this.actionData.fields[field]);
            });
        },

        setupScope: function (callback) {
            if (this.actionData.link) {
                let scope = this.actionData.link;

                this.scope = scope;

                if (!scope) {
                    throw new Error;
                }

                this.wait(true);

                this.getModelFactory().create(scope, model => {
                    this.model = model;

                    (this.actionData.fieldList || []).forEach(field => {
                        let attributes = (this.actionData.fields[field] || {}).attributes || {};

                        model.set(attributes, {silent: true});
                    });

                    this.linkList = this.getLinkList(scope);

                    if (this.isRendered()) {
                        this.controlLinkList();
                    } else {
                        this.once('after:render', () => {
                            this.controlLinkList();
                        });
                    }

                    callback();
                });

                return;

            }

            this.model = null;

            this.linkList = [];

            if (this.isRendered()) {
                this.controlLinkList();
            } else {
                this.once('after:render', () => {
                    this.controlLinkList();
                });
            }

            callback();
        },

        controlLinkList: function () {
            var $linkListCell = this.$el.find('.cell[data-name="linkList"]');
            var translatedOptions = {};

            if (this.linkList.length) {
                $linkListCell.removeClass('hidden');

                this.linkList.forEach(link => {
                    translatedOptions[link] = this.getLanguage().translate(link, 'links', this.scope);
                });

            } else {
                $linkListCell.addClass('hidden');
            }

            this.getView('linkList').setOptionList(this.linkList);
            this.getView('linkList').setTranslatedOptions(translatedOptions);
            this.getView('linkList').reRender();
        },

        getLinkList: function (scope) {
            var targetEntity = this.entityType;

            var linkDefs = this.getMetadata().get(['entityDefs', scope, 'links']) || {};
            var fieldDefs = this.getMetadata().get(['entityDefs', scope, 'fields']) || {};

            var linkList = [];

            Object.keys(linkDefs).forEach(link => {
                var foreignEntity = (linkDefs[link] || {}).entity;
                var type = (linkDefs[link] || {}).type;

                if (type === 'belongsToParent') {
                    if (~((fieldDefs[link] || {}).entityList || []).indexOf(targetEntity)) {
                        linkList.push(link);
                    }
                } else {
                    if (foreignEntity === targetEntity) {
                        linkList.push(link);
                    }
                }
            });

            return linkList;
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.hasFormulaAvailable = !!this.getMetadata().get('app.formula.functionList');

            this.wait(true);

            this.setupScope(() => {
                this.wait(false);
            });

            var model = new Model();

            model.name = 'Workflow';
            this.actionModel = model;

            if (this.actionType === 'createEntity') {
                model.set('linkList', this.actionData.linkList || []);

                this.createView('linkList', 'views/fields/multi-enum', {
                    model: model,
                    el: this.getSelector() + ' .field[data-name="linkList"]',
                    mode: 'edit',
                    defs: {name: 'linkList'}
                });
            }
        },

        addField: function (field, fieldData, isNew) {
            var fieldType = this.getMetadata().get('entityDefs.' + this.scope + '.fields.' + field + '.type') || 'base';
            var type = this.getMetadata().get('entityDefs.Workflow.fieldDefinitions.' + fieldType) || 'base';

            fieldData = fieldData || false;

            let escapedField = this.getHelper().escapeString(field);

            var fieldNameHtml = '<label>' + this.translate(escapedField, 'fields', this.scope) + '</label>';

            var removeLinkHtml =
                '<a role="button" tabindex="0" class="pull-right" data-action="removeField" ' +
                'data-field="' + escapedField + '"><span class="fas fa-times"></span></a>';

            var html = '<div class="margin clearfix field-row" ' +
                'data-field="' + escapedField + '" style="margin-left: 20px;">' + removeLinkHtml + fieldNameHtml +
                '<div class="field-container field" data-field="' + escapedField + '"></div></div>';

            this.$fieldDefinitions.append($(html));

            this.createView(
                'field-' + field, 'advanced:views/workflow/field-definitions/' +
                    Espo.Utils.camelCaseToHyphen(type),
                {
                    el: this.options.el + ' .field-container[data-field="' + field + '"]',
                    fieldData: fieldData,
                    model: this.model,
                    field: field,
                    entityType: this.entityType,
                    scope: this.scope,
                    type: type,
                    fieldType: fieldType,
                    isNew: isNew,
                }, view => {
                    view.render();
                });
        },

        handleLink: function () {
            var link = this.actionData.link;

            if (!link) {
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
                    targetEntityType: this.actionData.link
                }, view => {
                    view.render();
                });
            }
        },

        getFieldList: function () {
            var fieldDefs = this.getMetadata().get('entityDefs.' + this.scope + '.fields') || {};

            return Object.keys(fieldDefs)
                .filter(field => {
                    var type = fieldDefs[field].type;

                    if (fieldDefs[field].workflowDisabled) {
                        return false;
                    }

                    if (fieldDefs[field].disabled || fieldDefs[field].utility) {
                        return false;
                    }

                    if (fieldDefs[field].directAccessDisabled) {
                        return false;
                    }

                    if (fieldDefs[field].directUpdateDisabled) {
                        return false;
                    }

                    return !~['currencyConverted', 'autoincrement', 'map', 'foreign'].indexOf(type);
                })
                .sort((v1, v2) => {
                    return this.translate(v1, 'fields', this.scope)
                        .localeCompare(this.translate(v2, 'fields', this.scope));
                });
            },

        getLinkOptionsHtml: function () {
            var value = this.actionData.link;

            var html = '<option value="">--' + this.translate('Select') + '--</option>';

            this.getEntityList().forEach(entityName => {
                var label = this.translate(entityName, 'scopeNames');

                html += '<option value="' + entityName + '" ' + (entityName === value ? 'selected' : '') + '>' +
                    label + '</option>';
            });

            return html;
        },

        fetch: function () {
            var isValid = true;

            (this.actionData.fieldList || []).forEach(field => {
                isValid = this.getView('field-' + field).fetch();

                this.actionData.fields[field] = this.getView('field-' + field).fieldData;
            });

            if (this.hasFormulaAvailable) {
                if (this.actionData.link) {
                    var formulaView = this.getView('formula');

                    if (formulaView) {
                        this.actionData.formula = formulaView.fetch().formula;
                    }
                }
            }

            if (this.actionType === 'createEntity') {
                this.actionData.linkList = this.actionModel.get('linkList');
                this.actionData.entityType = this.actionData.link;
            }

            return isValid;
        },

        getEntityList: function() {
            var scopes = this.getMetadata().get('scopes');

            return Object.keys(scopes)
                .filter(scope => {
                    var defs = scopes[scope];

                    return (defs.entity && (defs.tab || defs.object || defs.workflow));
                })
                .sort((v1, v2) => {
                    return this.translate(v1, 'scopeNamesPlural')
                        .localeCompare(this.translate(v2, 'scopeNamesPlural'));
                });
        },

        changeLinkAction: function (e) {
            this.actionData.link = e.currentTarget.value;

            this.actionData.fieldList.forEach(field => {
                this.$el.find('.field-row[data-field="' + field + '"]').remove();

                this.clearView('field-' + field);
            });

            this.actionData.fieldList = [];
            this.actionData.fields = {};

            this.actionData.linkList = [];

            if (this.actionType === 'createEntity') {
                this.actionModel.set('linkList', []);
            }

            this.handleLink();
        },
    });
});
