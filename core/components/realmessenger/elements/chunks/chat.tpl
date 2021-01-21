<li class="realmessenger-chat {$class}" data-id={$id}>
    <div class="realmessenger-chat-header" style="width:100%;">
        <span class="realmessenger-chat-close btn" style="float:right;">x</span>
    </div>
    <div class="realmessenger-chat-body">
        <img src="{if !$user.photo}/assets/components/realmessenger/img/no_foto.png{else}{$user.photo}{/if}" alt="{$user.$fullname}">
        <span class="description">
            <strong>{$user.fullname}</strong><br>
        </span>
        <span class="badge messages-new-count " {if !$messages_new_count}style="display:none;"{/if}>{$messages_new_count}</span>
    </div>
    
</li>