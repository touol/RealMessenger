<form id="realmessenger-message-form" action="" method="post" >
	
    <input type="hidden" name="thread" value="[[+thread]]" />
	<input type="hidden" name="parent" value="0" />
	<input type="hidden" name="id" value="0" />
    
	<textarea name="text" id="realmessenger-message-editor"  placeholder="Введите текст"></textarea>
	
    <input type="submit" class="btn btn-primary submit" value="Отправить" title="Ctrl + Shift + Enter" />
	
	<div class="wrap_file"><input type="file"></div>
</form>

[[!ufForm?
        &anonym=`1`
    	&class=`modUser`
    	&list=`comment-[[+thread]]`
    	&tplForm=`LK2.uf.form`
    	&allowedFiles=`jpg,jpeg,png,gif,doc,pdf,txt,xlsx,jnt,docx,zip,xls`
    	&dropzone=`{"maxFilesize":2,"maxFiles":5,"acceptedFiles":".jpg, .jpeg, .gif, .png, .docx, .doc, .pdf, .xls, .xlsx","template":"edit"}`
]]