<div class="realmessenger-file">
    <a href="{$url}" target="_blank">
        {if $type == 'pdf'}<img src="/assets/components/realmessenger/img/pdf.png" alt="">{/if}
        {if $type == 'xls' OR $type == 'xlsx'}<img src="/assets/components/realmessenger/img/x.png" alt="">{/if}
        {if $type == 'docx' OR $type == 'doc'}<img src="/assets/components/realmessenger/img/w.png" alt="">{/if}
        {if $type == 'jpg' OR $type == 'png' OR $type == 'jpeg' OR $type == 'gif'}
            <img src="/assets/components/realmessenger/img/i.png" alt="{$thumb}">
        {/if}
    </a>
</div>