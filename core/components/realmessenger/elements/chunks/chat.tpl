<li class="realmessenger-chat {$class} clearfix" data-id={$id}>
    <span class="realmessenger-chat-close btn"></span>
    <div class="realmessenger-chat-body"> 
        <div class="description">
            <div class="realmessenger-chat__avatar" style="background-image: url({if !$user.photo}/assets/components/realmessenger/img/no_foto.png{else}{$user.photo}{/if});">
                {if $user.statuson}
                <span class="realmessenger-chat-status {if $user.status}realmessenger-online{else}realmessenger-offline{/if}" data-user_id={$user.id}></span>
                {/if}
            </div>
            <span class="realmessenger__user-fullname">{$user.fullname}</span>
            <div class="realmessenger__mess-count" {if !$messages_new_count}style="display:none;"{/if}>{$messages_new_count}</div>
        </div>
        {*<span class="db realmessenger__user-position"><b>Специальность:</b> <span>Врач функциональной диагностики</span></span>*}
        <div class="last-message">
            {$last_message}
        </div>
    </div>
    
</li>