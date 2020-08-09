<a href="{if $purpose_url}{$purpose_url}{else}{$url}{/if}">
    <div class="name">
    	<span>{$json.fullname}</span>
    </div>
    <div class="ticket-comment-body">
    	<p><time datetime="{$time}">{$time  | date_format : '%d.%m.%Y %H:%M'}</time>
            {$json.text | truncate}
        </p>
    </div>
</a>