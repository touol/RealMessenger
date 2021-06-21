<div id="realmessenger-message-form-wrapper" {if !$chat}style="display:none;"{/if}>
    {set $hash = '' | date : 'd.m.Y H:s' | md5}
    {'!ufForm' | snippet : [
            'anonym'=>1,
            'class'=>'modUser',
            'list'=>'chats-' ~ $hash,
            'tplForm'=>'uf.form',
            'allowedFiles'=>'jpg,jpeg,png,gif,doc,pdf,txt,xlsx,jnt,docx,zip,xls'
            'dropzone'=>'{ "maxFilesize":2,"maxFiles":5,"acceptedFiles":".jpg, .jpeg, .gif, .png, .docx, .doc, .pdf, .xls, .xlsx","template":"edit"}',
    ]}
    <form id="realmessenger-message-form" action="" method="post">
        
        <input type="hidden" name="chat" value="{$chat}" />
        <input type="hidden" name="id" value="0" />
        <input type="hidden" name="file_ids" value="0" />
        
        <textarea name="text" id="realmessenger-message-editor"  class="form-control textarea" placeholder="Введите текст"></textarea>
        
        <div class="realmessenger-message-form-footer"> 
            <button class="btn-smile form-footer__icon-btn"></button>
            <button class="btn-clip form-footer__icon-btn js__btn-clip"></button>
            <input type="submit" class="btn btn-primary submit" value="Отправить" title="Ctrl + Shift + Enter" />
        </div>
        
    </form>
    
</div>