<div id="sw_vendors_extra">
    <div class="sw-vendor-settings">
        {if $vendors_settings.sections}
            {include file="views/setup_wizard/components/setup_wizard_form.tpl" tab_id="vendors" tab=$vendors_settings}
        {/if}
    </div>
    <div class="sw-columns-block">
        <div id="container_sw_vendor_location_state" class="control-group">
            <div id="sw_vendor_locations_from">
                <form name="sw_vendor_location_from" class="form-horizontal cm-ajax cm-ajax-force" action="{""|fn_url}" method="post">
                    <input type="hidden" name="dispatch" value="setup_wizard.install_vendor_locations" />
                    <input type="hidden" name="result_ids" value="sw_vendor_locations_from" />

                    <div class="control-group">
                        <h2 class="sw-block-title">{__("sw.location")}</h2>
                    </div>
                    <div class="control-group">
                        <label for="sw_vendor_location_state" class="control-label ">{__("sw.enable_vendor_location_using_google_map")}:</label>

                        <div class="controls">
                            <input type="hidden" name="vendor_locations_state" value="N" />
                            {include file="common/switcher.tpl"
                                checked=($addons.vendor_locations.status === "ObjectStatuses::ACTIVE"|enum)
                                input_name="vendor_locations_state"
                                input_value="Y"
                                input_id="sw_vendor_location_state"
                                input_class="cm-submit"
                            }
                            {if ($addons.vendor_locations.status === "ObjectStatuses::ACTIVE"|enum)}
                                {include file="buttons/button.tpl" but_href="addons.manage#groupvendor_locations" but_text=__("sw.configure") but_role="action" but_target="_blank" but_meta="shift-left"}
                            {/if}
                        </div>
                    </div>
                </form>
            <!--sw_vendor_locations_from--></div>
        </div>
    </div>
    <div class="sw-columns-block">
            <div class="form-horizontal">
                <div class="control-group">
                    <h2 class="sw-block-title">{__("sw.vendor_profile_fields")}</h2>
                </div>
                <div class="control-group">
                    <label class="control-label">{__("sw.set_up_vendor_profile_fields")}</label>
                    <div class="controls">
                        {include file="buttons/button.tpl" but_href="profile_fields.manage?profile_type=S" but_text=__("sw.configure") but_role="action" but_target="_blank"}
                    </div>
                </div>
        </div>
    </div>
    <div class="apply-button">
        <button class="btn btn-primary btn-large ladda-button cm-submit" data-style="slide-right" data-ca-target-form="setup_wizard_vendors_form_elm"><span class="ladda-label">{__("save")}</span></button>
        <span class="sw-notifications-box"></span>
    </div>
<!--sw_vendors_extra--></div>
