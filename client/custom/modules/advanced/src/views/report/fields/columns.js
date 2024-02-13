
define(
    'advanced:views/report/fields/columns',
    ['views/fields/multi-enum', 'advanced:views/report/fields/group-by'],
    function (Dep, GroupBy) {

    return Dep.extend({

        fieldTypeList: [
            'currencyConverted',
            'int',
            'float',
            'duration',
            'enumInt',
            'enumFloat',
            'enum',
            'varchar',
            'link',
            'date',
            'datetime',
            'datetimeOptional',
            'email',
            'phone',
            'url',
            'personName',
            'array',
            'multiEnum',
            'checklist',
            'urlMultiple',
        ],

        numericFieldTypeList: [
            'currencyConverted',
            'int',
            'float',
            'enumInt',
            'enumFloat',
            'duration',
        ],

        setupOptions: function () {
            var entityType = this.model.get('entityType');

            var fields = this.getMetadata().get(['entityDefs', entityType, 'fields']) || {};

            var skipForeing = false;
            var version = this.getConfig().get('version') || '';

            var arr = version.split('.');

            if (version !== 'dev' && arr.length > 2 && parseInt(arr[0]) * 100 + parseInt(arr[1]) < 506) {
                skipForeing = true;
            }

            var noEmailField = false;

            if (
                version !== 'dev' && arr.length > 2 &&
                parseInt(arr[0]) * 100 + parseInt(arr[1]) * 10 +  parseInt(arr[2]) < 562
            ) {
                noEmailField = true;
            }

            var itemList = [];

            itemList.push('COUNT:id');

            var fieldList = Object.keys(fields) || [];

            fieldList = fieldList.sort((v1, v2) =>  {
                return this.translate(v1, 'fields', entityType)
                    .localeCompare(this.translate(v2, 'fields', entityType));
            });

            fieldList.forEach(field => {
                if (fields[field].disabled) {
                    return;
                }

                if (fields[field].directAccessDisabled) {
                    return;
                }

                if (fields[field].reportDisabled) {
                    return;
                }

                if (
                    this.getFieldManager().isEntityTypeFieldAvailable &&
                    !this.getFieldManager().isEntityTypeFieldAvailable(entityType, field)
                ) {
                    return;
                }

                if (~this.numericFieldTypeList.indexOf(fields[field].type)) {
                    itemList.push('SUM:' + field);
                    itemList.push('MAX:' + field);
                    itemList.push('MIN:' + field);
                    itemList.push('AVG:' + field);
                }
            });

            var groupBy = this.model.get('groupBy') || [];

            groupBy.forEach(foreignGroup => {
                if (!skipForeing) {
                    var links = this.getMetadata().get(['entityDefs', entityType, 'links']) || {};
                    var linkList = Object.keys(links);

                    linkList.sort((v1, v2) => {
                        return this.translate(v1, 'links', entityType)
                            .localeCompare(this.translate(v2, 'links', entityType));
                    });

                    linkList.forEach(link => {
                        if (links[link].type !== 'belongsTo') {
                            return;
                        }

                        if (link !== foreignGroup) {
                            return;
                        }

                        var scope = links[link].entity;

                        if (!scope) {
                            return;
                        }

                        if (
                            links[link].disabled ||
                            links[link].utility
                        ) {
                            return;
                        }

                        var fields = this.getMetadata().get(['entityDefs', scope, 'fields']) || {};
                        var fieldList = Object.keys(fields);

                        fieldList.sort((v1, v2) => {
                            return this.translate(v1, 'fields', scope)
                                .localeCompare(this.translate(v2, 'fields', scope));
                        });

                        fieldList.forEach(field => {
                            if (
                                fields[field].disabled ||
                                fields[field].utility
                            ) {
                                return;
                            }

                            if (fields[field].directAccessDisabled) {
                                return;
                            }
                            if (fields[field].reportDisabled) {
                                return;
                            }

                            if (
                                this.getFieldManager().isEntityTypeFieldAvailable &&
                                !this.getFieldManager().isEntityTypeFieldAvailable(scope, field)
                            ) {
                                return;
                            }

                            if (~this.fieldTypeList.indexOf(fields[field].type)) {
                                if (fields[field].type === 'enum' && field.substr(-8) === 'Currency') {
                                    return;
                                }

                                if (noEmailField && fields[field].type === 'email') {
                                    return;
                                }

                                if (noEmailField && fields[field].type === 'phone') {
                                    return;
                                }
                                if (field === 'name') {
                                    return;
                                }

                                itemList.push(link + '.' + field);
                            }
                        });
                    });
                }
            });

            fieldList.forEach(field => {
                if (groupBy.length > 1) {
                    return;
                }

                if (
                    fields[field].disabled ||
                    fields[field].reportDisabled ||
                    this.getFieldManager().isEntityTypeFieldAvailable &&
                    !this.getFieldManager().isEntityTypeFieldAvailable(entityType, field)
                ) {
                    return;
                }

                if (!~this.fieldTypeList.indexOf(fields[field].type)) {
                    return;
                }

                itemList.push(field);
            });

            var links = this.getMetadata().get(['entityDefs', entityType, 'links']) || {};
            var linkList = Object.keys(links);

            linkList.sort((v1, v2) => {
                return this.translate(v1, 'links', entityType)
                    .localeCompare(this.translate(v2, 'links', entityType));
            });

            linkList.forEach(link => {
                if (links[link].type !== 'belongsTo' && links[link].type !== 'hasOne') {
                    return;
                }

                if (
                    links[link].disabled ||
                    links[link].utility
                ) {
                    return;
                }

                var subEntityType = links[link].entity;

                if (!subEntityType) {
                    return;
                }

                var fields = this.getMetadata().get(['entityDefs', subEntityType, 'fields']) || {};

                var fieldList = Object.keys(fields) || [];

                fieldList = fieldList.sort((v1, v2) => {
                    return this.translate(v1, 'fields', subEntityType)
                        .localeCompare(this.translate(v2, 'fields', subEntityType));
                });

                fieldList.forEach(field => {
                    if (
                        fields[field].disabled ||
                        fields[field].utility
                    ) {
                        return;
                    }

                    if (fields[field].directAccessDisabled) {
                        return;
                    }

                    if (fields[field].reportDisabled) {
                        return;
                    }

                    if (
                        this.getFieldManager().isEntityTypeFieldAvailable &&
                        !this.getFieldManager().isEntityTypeFieldAvailable(subEntityType, field)
                    ) {
                        return;
                    }

                    if (~this.numericFieldTypeList.indexOf(fields[field].type)) {
                        itemList.push('SUM:' + link + '.' + field);
                        itemList.push('MAX:' + link + '.' +  field);
                        itemList.push('MIN:' + link + '.' +  field);
                        itemList.push('AVG:' + link + '.' +  field);
                    }
                });
            });

            this.params.options = itemList;
        },

        setupTranslatedOptions: function (customEntityType) {
            GroupBy.prototype.setupTranslatedOptions.call(this, customEntityType);

            this.params.options.forEach(item => {
                if (item === 'COUNT:id') {
                    this.translatedOptions[item] = this.translate('COUNT', 'functions', 'Report').toUpperCase();
                }
            });
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.setupOptions();
            this.setupTranslatedOptions();

            this.listenTo(this.model, 'change', model => {
                if (model.hasChanged('groupBy')) {
                    this.setupOptions();
                    this.setupTranslatedOptions();
                    this.reRender();
                }
            });
        },
    });
});
