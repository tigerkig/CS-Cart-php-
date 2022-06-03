{include file="views/profiles/components/profiles_scripts.tpl"}
{capture name="mainbox_title"}{__("organizations.organization")}{/capture}

<div class="ty-account">
    <form name="organization_update_form" enctype="multipart/form-data" action="{""|fn_url}" method="post">

        {include file="addons/organizations/components/organizations_fields.tpl"
            base_input_name="organization_data"
            organization_profile_fields=$profile_fields
            organization_data=$organization->toArray()
        }

        <div class="ty-profile-field__buttons buttons-container">
            {include file="buttons/save.tpl" but_name="dispatch[organizations.update]" but_meta="ty-btn__secondary" but_id="save_organization_but"}
        </div>
    </form>
</div>