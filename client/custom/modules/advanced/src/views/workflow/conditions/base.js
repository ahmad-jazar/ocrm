
define('advanced:views/workflow/conditions/base', ['view'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/base',

        defaultConditionData: {
            comparison: 'equals',
            subjectType: 'value',
        },

        comparisonList: [
            'equals',
            'wasEqual',
            'notEquals',
            'wasNotEqual',
            'changed',
            'notChanged',
            'notEmpty',
        ],

        events: {
            'change [data-name="comparison"]': function (e) {
                this.setComparison(e.currentTarget.value);
                this.handleComparison(e.currentTarget.value);
            },
            'change [data-name="subjectType"]': function (e) {
                this.setSubjectType(e.currentTarget.value);
                this.handleSubjectType(e.currentTarget.value);
            },
            'change [data-name="subject"]': function (e) {
                this.setSubject(e.currentTarget.value);
                this.handleSubject(e.currentTarget.value);
            },
        },

        data: function () {
            return {
                field: this.field,
                entityType: this.entityType,
                comparisonValue: this.conditionData.comparison,
                comparisonList: this.comparisonList,
                readOnly: this.readOnly,
            };
        },

        setupComparisonList: function () {
            if (this.isComplexField || this.options.isChangedDisabled) {
                var comparisonList = [];

                Espo.Utils.clone(this.comparisonList).forEach(item => {
                    if (~['changed', 'notChanged', 'wasEqual', 'wasNotEqual'].indexOf(item)) {
                        return;
                    }

                    comparisonList.push(item);
                });

                this.comparisonList = comparisonList;
            }
        },

        setup: function () {
            this.conditionType = this.options.conditionType;
            this.conditionData = this.options.conditionData || {};

            this.field = this.options.field;
            this.entityType = this.options.entityType;
            this.type = this.options.type;
            this.fieldType = this.options.fieldType;
            this.readOnly = this.options.readOnly;

            this.isComplexField = !!(~this.field.indexOf('.'));

            this.comparisonList = Espo.Utils.clone(this.comparisonList);
            this.setupComparisonList();

            if (this.options.isNew) {
                var cloned = {};

                for (var i in this.defaultConditionData) {
                    cloned[i] = Espo.Utils.clone(this.defaultConditionData[i]);
                }

                this.conditionData = _.extend(cloned, this.conditionData);
            }

            this.conditionData.fieldToCompare = this.field;
        },

        afterRender: function () {
            this.handleComparison(this.conditionData.comparison, true);

            this.$comparison = this.$el.find('[data-name="comparison"]');
        },

        fetchComparison: function () {
            var $comparison = this.$el.find('[data-name="comparison"]');

            if ($comparison.length) {
                this.conditionData.comparison = $comparison.val();
            }
        },

        fetchSubjectType: function () {
            var $subjectType = this.$el.find('[data-name="subjectType"]');

            if ($subjectType.length) {
                this.conditionData.subjectType = $subjectType.val();
            }
        },

        fetchSubject: function () {
            delete this.conditionData.value;
            delete this.conditionData.field;

            if ('fetch' in (this.getView('subject') || {})) {
                var data = this.getView('subject').fetch() || {};

                for (var attr in data) {
                    this.conditionData[attr] = data[attr];
                }

                return;
            }

            var $subject = this.$el.find('[data-name="subject"]');

            if ($subject.length) {
                switch (this.conditionData.subjectType) {
                    case 'field':
                        this.conditionData.field = $subject.val();
                        break;
                    case 'value':
                        this.conditionData.value = $subject.val();
                        break;
                }

            }
        },

        fetch: function () {
            this.fetchComparison();
            this.fetchSubjectType();
            this.fetchSubject();

            return this.conditionData;
        },

        setComparison: function (comparison) {
            this.conditionData.comparison = comparison;
        },

        setSubjectType: function (subjectType) {
            this.conditionData.subjectType = subjectType;
        },

        setSubject: function (subject) {
            this.conditionData.subject = subject;
        },

        handleComparison: function (comparison, noFetch) {
            switch (comparison) {
                case 'changed':
                case 'notChanged':
                case 'notEmpty':
                case 'isEmpty':
                case 'empty':
                case 'true':
                case 'false':
                case 'today':
                case 'beforeToday':
                case 'afterToday':
                    this.$el.find('.subject-type').empty();
                    this.$el.find('.subject').empty();

                    break;

                case 'equals':
                case 'wasEqual':
                case 'notEquals':
                case 'wasNotEqual':
                case 'greaterThan':
                case 'lessThan':
                case 'greaterThanOrEquals':
                case 'lessThanOrEquals':
                case 'has':
                case 'notHas':
                case 'contains':
                case 'notContains':
                    this.createView('subjectType', 'advanced:views/workflow/condition-fields/subject-type', {
                        el: this.options.el + ' .subject-type',
                        value: this.conditionData.subjectType,
                        readOnly: this.readOnly,
                    }, view => {
                        view.render(() => {
                            if (!noFetch) {
                                this.fetch();
                            }

                            this.handleSubjectType(this.conditionData.subjectType, noFetch);
                        });
                    });

                    break;
            }
        },

        getSubjectInputViewName: function (subjectType) {
            return 'advanced:views/workflow/condition-fields/subjects/text-input';
        },

        handleSubjectType: function (subjectType, noFetch) {
            switch (subjectType) {
                case 'value':
                    this.createView('subject', this.getSubjectInputViewName(subjectType), {
                        el: this.options.el + ' .subject',
                        entityType: this.entityType,
                        field: this.field,
                        value: this.getSubjectValue(),
                        conditionData: this.conditionData,
                        readOnly: this.readOnly,
                    }, view => {
                        view.render(() => {
                            if (!noFetch) {
                                this.fetch();
                            }

                            this.handleSubject(this.conditionData.subject, noFetch);
                        });
                    });

                    break;

                case 'field':
                    this.createView('subject', 'advanced:views/workflow/condition-fields/subjects/field', {
                        el: this.options.el + ' .subject',
                        entityType: this.options.originalEntityType || this.entityType,
                        value: this.conditionData.field,
                        fieldType: this.fieldType,
                        field: this.field,
                        readOnly: this.readOnly,
                    }, view => {
                        view.render(() => {
                            this.fetch();
                        });
                    });

                    break;

                default:
                    this.$el.find('.subject').empty();
            }
        },

        handleSubject: function (subject, noFetch) {
            if (!noFetch) {
                this.fetch();
            }
        },

        getSubjectValue: function () {
            return this.conditionData.value;
        },
    });
});
