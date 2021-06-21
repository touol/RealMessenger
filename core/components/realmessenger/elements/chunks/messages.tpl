<div id="realmessenger-messages-wrapper">
    <div class="realmessenger__chat-info">
        <button class="back-btn js__back-to-chat"><span></span></button>
        <div class="realmessenger__chat-info__user">
            <span class="realmessenger__chat-info__user-name">
                Александр Туниеков
            </span>
            <span class="realmessenger__chat-info__user-status">online</span>
        </div> 
    </div>
    <ul class="chat_list" id="realmessenger-messages">
        {$messages}
    </ul>
    <p class="realmessenger-messages-empty" {if $messages}style="display:none;"{/if}> У Вас нет сообщений. Напишите новое!</p>
</div>