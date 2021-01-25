<div class="form-group realmessenger-autocomplect" data-search_goal="{if $search_goal}{$search_goal}{else}contact{/if}">
    <label class="control-label" for="search">{if $search_goal_label}{$search_goal_label}{else}Поиск контактов{/if}</label>
    <div class="input-group">
    
        <input type="search" class="form-control realmessenger-autocomplect-content" 
        placeholder="{if $search_goal_label}{$search_goal_label}{else}Поиск контактов{/if}" 
        />
        <div class="input-group-btn">
            <button class="btn realmessenger-autocomplect-all">
                <span class="caret"></span>
            </button>
            
        </div>
        <ul class="dropdown-menu realmessenger-autocomplect-menu" role="menu">
            
        </ul>
    </div>
</div>