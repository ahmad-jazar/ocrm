
define('advanced:views/workflow/conditions/date', ['advanced:views/workflow/conditions/base'], function (Dep) {

    return Dep.extend({

        template: 'advanced:workflow/conditions/date',

        comparisonList: [
            'on',
            'before',
            'after',
            'today',
            'beforeToday',
            'afterToday',
            'isEmpty',
            'notEmpty',
            'changed',
            'notChanged'
        ],

        defaultConditionData: {
            comparison: 'on',
            subjectType: 'today',
            shiftDays: 0,
        },

        setup: function () {
            Dep.prototype.setup.call(this);

            this.events['change [data-name="shiftDays"]'] = e => {
                this.setShiftDays(e.currentTarget.value);
                this.handleShiftDays(e.currentTarget.value);
            };
        },

        afterRender: function () {
            Dep.prototype.afterRender.call(this);
            this.handleShiftDays(this.conditionData.shiftDays, true);
        },

        handleComparison: function (comparison, noFetch) {
            Dep.prototype.handleComparison.call(this, comparison, noFetch);

            switch (comparison) {
                case 'on':
                case 'before':
                case 'after':
                    this.$el.find('.subject').empty();

                    this.createView('subjectType', 'advanced:views/workflow/condition-fields/subject-type-date', {
                        el: this.options.el + ' .subject-type',
                        value: this.conditionData.subjectType,
                        readOnly: this.readOnly
                    }, function (view) {
                        view.render(function() {
                            if (!noFetch) {
                                this.fetch();
                            }
                            this.handleSubjectType(this.conditionData.subjectType);
                        }.bind(this));
                    }.bind(this));

                    this.createView('shiftDays', 'advanced:views/workflow/condition-fields/shift-days', {
                        el: this.options.el + ' .shift-days',
                        entityType: this.entityType,
                        field: this.field,
                        value: this.conditionData.shiftDays || 0,
                        readOnly: this.readOnly
                    }, function (view) {
                        view.render(function () {
                            if (!noFetch) {
                                this.fetch();
                                this.handleShiftDays(this.conditionData.subject);
                            }
                        }.bind(this));
                    }.bind(this));

                    break;
                default:
                    this.$el.find('.shift-days').empty();
            }
        },

        setShiftDays: function (shiftDays) {
            this.conditionData.shiftDays = shiftDays;
        },

        fetch: function () {
            Dep.prototype.fetch.call(this);
            this.fetchShiftDays();
            return this.conditionData;
        },

        fetchShiftDays: function () {
            var $shiftDays = this.$el.find('[data-name="shiftDays"]');

            if ($shiftDays.length) {
                this.conditionData.shiftDays = parseInt($shiftDays.val()) || 0;

                var $shiftDaysOperator = this.$el.find('[data-name="shiftDaysOperator"]');

                if ($shiftDaysOperator.val() === 'minus') {
                    this.conditionData.shiftDays = (-1) * this.conditionData.shiftDays;
                }
            }
        },

        handleShiftDays: function (shiftDays, noFetch) {
            if (!noFetch) {
                this.fetch();
            }
        },

    });
});
