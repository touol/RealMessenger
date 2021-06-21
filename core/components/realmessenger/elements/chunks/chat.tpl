<li class="realmessenger-chat {$class} clearfix" data-id={$id}>
    <!--div class="realmessenger-chat-header" style="width:100%;">
        
    </div-->
    <span class="realmessenger-chat-close btn"></span>
    <div class="realmessenger-chat-body"> 
        <div class="description">
            <div class="realmessenger-chat__avatar" style="background-image: url({if !$user.photo}/assets/components/realmessenger/img/no_foto.png{else}{$user.photo}{/if});">
                <!--img src="{if !$user.photo}/assets/components/realmessenger/img/no_foto.png{else}{$user.photo}{/if}" alt="{$user.$fullname}"-->
                <span class="realmessenger-chat-status realmessenger-online"></span>
            </div>
            <span class="realmessenger__user-fullname">{$user.fullname}</span>
            <div class="realmessenger__mess-count">1</div>
        </div>
        <span class="db realmessenger__user-position"><b>Специальность:</b> <span>Врач функциональной диагностики</span></span>
        <div class="last-message"><span>Слышно номально :-) Здесь вы можете создать/отредактировать чанк. Помните, чанки – чистый HTML-код, и любые PHP-скрипты выполняться в них не будут.</span></div>
        <span class="badge messages-new-count " {if !$messages_new_count}style="display:none;"{/if}>{$messages_new_count}</span>
    </div>
    
</li>