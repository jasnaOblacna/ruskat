{if isset($categoryPictureStyle) and ($categoryPictureStyle eq '2')}
    <div class="block-category card card-block hidden-sm-down" style="background: url('{$category.image.large.url}'); background-size: cover; background-position: top center;">
      <h1 class="h1">{$category.name}</h1>
      {if $category.description}
        <div id="category-description" class="text-muted">
          {$category.description nofilter}
        </div>
      {/if}
    </div>
{elseif isset($categoryPictureStyle) and ($categoryPictureStyle eq '1')}  
<div class="block-category card card-block hidden-sm-down">
    <h1 class="h1">{$category.name}</h1>
    {if $category.description || $category.image.large.url}
      <div id="category-description" class="text-muted">
        {$category.description nofilter}
        {if $category.image.large.url}
        <img src="{$category.image.large.url}" alt="{if $category.image.legend != ''}{$category.image.legend}{else}{$category.name}{/if}">
        {/if}
      </div>
    {/if}
  </div>
{else}{*end categoryPictureStyle*}
  <div class="block-category card card-block hidden-sm-down">
    <h1 class="h1">{$category.name}</h1>
    {if $category.description || $category.image.large.url}
      <div id="category-description" class="text-muted">
        {if $category.image.large.url}
        <img src="{$category.image.large.url}" alt="{if $category.image.legend != ''}{$category.image.legend}{else}{$category.name}{/if}">
        {/if}
        {$category.description nofilter}
      </div>
    {/if}
  </div>
{/if}{*end categoryPictureStyle*}