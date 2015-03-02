 {* Тема оформления Experience v.1.0  для Alto CMS      *}
 {* @licence     CC Attribution-ShareAlike   *}

 <div class="widget-content" id="block_talk_search_content"
      {if !$_aRequest.submit_talk_filter}style="display:none;"{/if}>
     <form action="{router page='talk'}" method="GET" name="talk_filter_form">
         <div class="form-group">
             <div class="input-group">
                 <span class="input-group-addon">{$aLang.talk_filter_label_sender}</span>
                 <input type="text" placeholder="{$aLang.talk_filter_notice_sender}" id="talk_filter_sender" name="sender" value="{$_aRequest.sender}" class="form-control autocomplete-users-sep"/>
             </div>
         </div>

         <div class="form-group">
             <div class="input-group">
                 <span class="input-group-addon">{$aLang.talk_filter_label_keyword}</span>
                 <input type="text" placeholder="{$aLang.talk_filter_notice_keyword}" id="talk_filter_keyword" name="keyword" value="{$_aRequest.keyword}"
                        class="form-control"/>

             </div>
         </div>


         <div class="form-group">
             <div class="input-group">
                 <span class="input-group-addon">{$aLang.talk_filter_label_keyword_text}</span>
                 <input type="text" placeholder="{$aLang.talk_filter_notice_keyword}" id="talk_filter_keyword_text" name="keyword_text"
                        value="{$_aRequest.keyword_text}" class="form-control"/>

             </div>
         </div>


         <div class="row">
             <div class="col-md-12">
                 <div class="form-group has-feedback">
                     <div class="input-group charming-datepicker">
                         <span class="input-group-addon">{$aLang.talk_filter_label_date_start}</span>
                         <div class="dropdown-menu"></div>
                         <input class="date-picker form-control" data-toggle="dropdown" readonly="readonly"
                                type="text" id="talk_filter_start" name="start" value="{$_aRequest.start}"
                                 />
                         <span class="form-control-feedback" data-toggle="dropdown"><i class="fa fa-calendar-o"></i></span>
                     </div>
                 </div>
             </div>
             <div class="col-md-12">
                 <div class="form-group has-feedback">
                     <div class="input-group charming-datepicker">
                         <span class="input-group-addon">{$aLang.talk_filter_label_date_end}</span>
                         <div class="dropdown-menu"></div>
                         <input class="date-picker form-control" data-toggle="dropdown" readonly="readonly"
                                type="text" id="talk_filter_end" name="end" value="{$_aRequest.end}"
                                 />
                         <span class="form-control-feedback" data-toggle="dropdown"><i class="fa fa-calendar-o"></i></span>
                     </div>
                 </div>
             </div>
         </div>


         <div class="checkbox pal0">
             <label for="talk_filter_favourite">
                 <input type="checkbox" {if $_aRequest.favourite}checked="checked" {/if} name="favourite"
                        value="1" id="talk_filter_favourite"/>
                 {$aLang.talk_filter_label_favourite}
             </label>
         </div>
         <br/>

         <input type="submit" name="submit_talk_filter" value="{$aLang.talk_filter_submit}"
                class="btn btn-blue btn-normal corner-no pull-right"/>
         <input type="submit" name="" value="{$aLang.talk_filter_submit_clear}" class="btn btn-light btn-normal corner-no"
                onclick="return ls.talk.clearFilter();"/>
     </form>
     {if $oTalk}<br/><br/>{/if}
 </div>

