
define('advanced:views/report/fields/filters', ['views/fields/multi-enum'], function (Dep) {

    return Dep.extend({

        getFilterList: function () {
            var entityType = this.model.get('entityType');

            var fields = this.getMetadata().get('entityDefs.' + entityType + '.fields');

            var filterList = Object.keys(fields).filter(field => {
                if (this.options.skipLinkMultiple) {
                    if (fields[field].type === 'linkMultiple') {
                        return;
                    }
                }

                if (fields[field].type === 'map') {
                    return;
                }

                if (fields[field].disabled || fields[field].utility) {
                    return;
                }

                if (fields[field].reportDisabled) {
                    return;
                }

                if (fields[field].reportFilterDisabled) {
                    return;
                }

                if (fields[field].directAccessDisabled) {
                    return;
                }

                if (!this.getFieldManager().isEntityTypeFieldAvailable(entityType, field)) {
                    return;
                }

                return this.getFieldManager().checkFilter(fields[field].type);
            });

            filterList.sort((v1, v2) => {
                return this.translate(v1, 'fields', entityType)
                    .localeCompare(this.translate(v2, 'fields', entityType));
            });

            var links = this.getMetadata().get('entityDefs.' + entityType + '.links') || {};

            var linkList = Object.keys(links).sort((v1, v2) => {
                return this.translate(v1, 'links', entityType)
                    .localeCompare(this.translate(v2, 'links', entityType));
            });

            linkList.forEach(link => {
                var type = links[link].type

                if (type !== 'belongsTo' && type !== 'hasMany' && type !== 'hasChildren') {
                    return;
                }

                var scope = links[link].entity;

                if (!scope) {
                    return;
                }

                if (links[link].disabled || links[link].utility) {
                    return;
                }

                var fields = this.getMetadata().get('entityDefs.' + scope + '.fields') || {};

                var foreignFilterList = Object.keys(fields).filter(field => {
                    if (~['linkMultiple', 'linkParent', 'personName'].indexOf(fields[field].type)) {
                        return;
                    }

                    if (fields[field].reportDisabled) {
                        return;
                    }

                    if (fields[field].reportFilterDisabled) {
                        return;
                    }

                    if (fields[field].directAccessDisabled) {
                        return;
                    }

                    if (fields[field].foreignAccessDisabled) {
                        return;
                    }

                    if (!this.getFieldManager().isEntityTypeFieldAvailable(scope, field)) {
                        return;
                    }

                    return this.getFieldManager().checkFilter(fields[field].type) && !fields[field].disabled;
                });

                foreignFilterList.sort((v1, v2) => {
                    return this.translate(v1, 'fields', scope)
                        .localeCompare(this.translate(v2, 'fields', scope));
                });

                foreignFilterList.forEach(item => {
                    filterList.push(link + '.' + item);
                });
            });

            return filterList;
        },

        setupTranslatedOptions: function () {
            this.translatedOptions = {};

            var entityType = this.model.get('entityType');

            this.params.options.forEach(item => {
                var field = item;
                var scope = entityType;
                var isForeign = false;

                if (~item.indexOf('.')) {
                    isForeign = true;
                    field = item.split('.')[1];

                    var link = item.split('.')[0];

                    scope = this.getMetadata().get('entityDefs.' + entityType + '.links.' + link + '.entity');
                }

                this.translatedOptions[item] = this.translate(field, 'fields', scope);

                if (isForeign) {
                    this.translatedOptions[item] =  this.translate(link, 'links', entityType) + '.' +
                        this.translatedOptions[item];
                }
            });
        },

        setupOptions: function () {
            Dep.prototype.setupOptions.call(this);

            this.params.options = this.getFilterList();
            this.setupTranslatedOptions();
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);

            if (this.$element && this.$element[0] && this.$element[0].selectize) {
                this.$element[0].selectize.focus();
            }
        },
    });
});
