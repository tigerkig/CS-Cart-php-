{capture name="mailing_lists"}
    {assign var="show_newsletters_content" value=false}

    {hook name="newsletters:checkout_email_subscription"}
    <div class="subscription-container" id="subsciption_{$tab_id}">
        {foreach from=$page_mailing_lists item=list}
            {if $list.show_on_checkout}
                {assign var="show_newsletters_content" value=true}
            {/if}
            <input type="hidden" name="all_mailing_lists[]" value="{$list.list_id}" />

            <div class="ty-newsletters__item{if !$list.show_on_checkout} hidden{/if}">
                <label><input type="checkbox" name="mailing_lists[]" value="{$list.list_id}" {if $user_mailing_lists[$list.list_id]}checked="checked"{/if} class="checkbox cm-news-subscribe" />{$list.object}</label>
            </div>
        {/foreach}
    <!--subsciption_{$tab_id}--></div>
    {/hook}
{/capture}

{if $show_newsletters_content}
<div class="ty-newsletters">
    {include file="common/subheader.tpl" title=__("text_signup_for_subscriptions")}

    {$smarty.capture.mailing_lists nofilter}
</div>
{/if}
