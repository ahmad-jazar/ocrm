<div>
    <div class="actions{{#unless readOnly}} margin margin-bottom{{/unless}} no-side-margin"></div>
    {{#unless readOnly}}
    <div class="btn-group">
        <button
            class="btn btn-default btn-sm btn-icon radius-right"
            type="button"
            data-toggle="dropdown"
            title="{{translate 'Add Action' scope='Workflow'}}"
        ><span class="fas fa-plus"></span></button>
        <ul class="dropdown-menu">
        {{#each actionTypeList}}
            <li><a
                role="button"
                tabindex="0"
                data-action="addAction"
                data-type="{{this}}"
            >{{translate this scope="Workflow" category="actionTypes"}}</a></li>
        {{/each}}
        </ul>
    </div>
    {{/unless}}
</div>

{{#if showNoData}}
<div class="list-container margin-top">
    <div class="no-data">
        {{translate 'No Data'}}
    </div>
</div>
{{/if}}
