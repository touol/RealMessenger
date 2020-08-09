<div id="realmessenger-message-form-wrapper" {if !$chat}style="display:none;"{/if}>
    <form id="realmessenger-message-form" action="" method="post">
        
        <input type="hidden" name="chat" value="{$chat}" />
        <input type="hidden" name="id" value="0" />
        <input type="hidden" name="file_ids" value="0" />
        
        <textarea name="text" id="realmessenger-message-editor"  class="form-control" placeholder="Введите текст"></textarea>
        
        <input type="submit" class="btn btn-primary submit" value="Отправить" title="Ctrl + Shift + Enter" />
        
        <div class="wrap_file"><input type="file"></div>
    </form>
    {set $hash = '' | date : 'd.m.Y H:s' | md5}
    {'!ufForm' | snippet : [
            'anonym'=>1,
            'class'=>'modUser',
            'list'=>'chats-' ~ $hash,
            'tplForm'=>'LK2.uf.form',
            'allowedFiles'=>'jpg,jpeg,png,gif,doc,pdf,txt,xlsx,jnt,docx,zip,xls'
            'dropzone'=>'{ "maxFilesize":2,"maxFiles":5,"acceptedFiles":".jpg, .jpeg, .gif, .png, .docx, .doc, .pdf, .xls, .xlsx","template":"edit"}',
    ]}
</div>