
define('advanced:views/bpmn-flowchart-element/fields/start-direction', ['views/fields/enum'], function (Dep) {

    return Dep.extend({

        data: function () {
            var data = Dep.prototype.data.call(this);
            data.isNotEmpty = true;

            return data;
        },

        getValueForDisplay: function () {
            var value = Dep.prototype.getValueForDisplay.call(this);

            if (!value) {
                value = '';
            }

            return value;
        },

        fetch: function () {
            var data = Dep.prototype.fetch.call(this);

            if (data[this.name] === '') {
                data[this.name] = null;
            }

            return data;
        },
    });
});
