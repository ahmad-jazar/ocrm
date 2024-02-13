{{#if readOnly}}
    <code>
{{/if}}
<div
    class="field-container"
    style="display: inline-block;{{#unless readOnly}} min-width: 60%;{{/unless}}"
>{{{field}}}</div>
{{#if readOnly}}
    </code>
{{/if}}
