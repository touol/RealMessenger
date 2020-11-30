<li class="{if $ownmessage}ownmessage{/if} realmessenger-message {$messag_new}" 
    id="message-{$id}" data-id="{$id}">
	<div class="name">
		<a href="#"><img src="{if !$user.photo}/assets/components/realmessenger/img/no_foto.png{else}{$avatar}{/if}" alt="" height="38" width="37"></a>
		<span>{$fullname}</span>
	</div>
	<div class="realmessenger-message-body">
    	<p><time datetime="{$time}">{$date_ago}</time>
	        {$text}
	    </p>
	</div>
	
	 <div class="files">
        {if $file_ids}
            {$_modx->runSnippet('!pdoResources', [
                'class' => 'UserFile',
                'loadModels' => 'UserFiles',
                'limit' => 10,
                'tpl' => 'tpl.RealMessenger.file',
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
                'where' => [
                    "UserFile.class"=> "modUser",
                    "UserFile.id IN (" ~ $file_ids ~ ")",
                ],
                'sortby' => '{"rank":"ASC"}',
                'showLog' => 0
            ])}
        {/if}
    </div>
</li>