<li class="{$class}" data-id={$id}>
    <img src="{if !$user.photo}/assets/images/no_foto.png{else}{$user.photo}{/if}" alt="[[+fullname]]">
    <span class="vra-main">
        <strong>{$user.fullname}</strong><br>
    </span>
    <span class="badge messages-new-count ">{$messages_new_count}</span>
</li>