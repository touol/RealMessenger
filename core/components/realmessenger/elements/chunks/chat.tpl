<li class="realmessenger-chat {$class}" data-id={$id}>
    <img src="{if !$user.photo}/assets/images/no_foto.png{else}{$user.photo}{/if}" alt="{$user.$fullname}">
    <span class="description">
        <strong>{$user.fullname}</strong><br>
    </span>
    <span class="badge messages-new-count " {if !$messages_new_count}style="display:none;"{/if}>{$messages_new_count}</span>
</li>