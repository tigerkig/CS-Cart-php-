<div class="group">
    <div class="control-group">
        <label class="control-label" for="elm_organization">{__("organizations.organization")}</label>
        <div class="controls">
            {include file="addons/organizations/views/organizations/components/picker/picker.tpl"
                item_ids=[$search.organization_id]
                input_name="organization_id"
                show_empty_variant=true
            }
        </div>
    </div>
</div>