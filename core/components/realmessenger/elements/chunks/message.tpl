<li class="{if $ownmessage}ownmessage{/if} realmessenger-message {$comment_new}" 
    id="message-{$id}" data-id="{$id}">
	<div class="name">
		<a href="#"><img src="{$avatar}" alt="" height="38" width="37"></a>
		<span>{$fullname}</span>
	</div>
	<div class="realmessenger-message-body">
    	<p><time datetime="{$time}">{$date_ago}</time>
	        {$text}
	    </p>
	</div>
	
	 <div class="files">
                {*$_modx->runSnippet('!pdoResources', [
                    'class' => 'UserFile',
                    'loadModels' => 'UserFiles',
                    'limit' => 10,
                    'tpl' => 'tpl.file.item',
                    'leftJoin' => '{
                        "Thumb": {
                            "class": "UserFile",
                            "on": "Thumb.parent = UserFile.id AND Thumb.properties LIKE \'%w\":120,\"h\":90%\'"
                        }
                    }',
                    'select' => '{
                        "UserFile": "*",
                        "Thumb": "Thumb.url as thumb"
                    }',
                    'where' => '{
                        "UserFile.class": "modUser",
                        "UserFile.list": "comment-' ~ $id ~ '"
                        
                    }',
                    'sortby' => '{"rank":"ASC"}',
                ])*}  
        </div>
</li>