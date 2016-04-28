 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

{extends file="themes/$sSkinTheme/layouts/default_light.tpl"}

{block name="layout_vars"}
    {$noSidebar=true}
{/block}

{block name="layout_content"}
    <div class="text-center page-header">
        <h3>{$aLang.registration_invite}</h3>
    </div>

    <div class="row">
        <div class="col-xs-20 col-xs-offset-2 col-sm-12 col-sm-offset-6  col-md-10 col-md-offset-7  col-lg-10 col-lg-offset-7">
            <form action="{R::GetLink("registration")}invite/" method="POST">

                <div class="form-group">
                    <div class="input-group">
                        <label for="input-lnvite" class="input-group-addon"><i class="fa fa-paw"></i></label>
                        <input id="input-lnvite" type="text" placeholder="{$aLang.registration_invite_code}" name="invite_code" class="invite_code form-control" required>
                    </div>
                </div>

                <br/>
                <br/>

                <input type="submit" name="submit_invite" value="{$aLang.registration_invite_check}"
                       class="btn btn-blue btn-big corner-no"/>
            </form>
        </div>
    </div>
{/block}

