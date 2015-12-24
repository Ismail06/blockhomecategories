{if isset($categories) && !empty($categories)}
<div class="home-categories">

	<h3>{l s='Popular categories' mod='blockhomecategories'}</h3>

	{foreach from=$categories item=category}
	<div class="category-block">
		<a href="{$link->getCategoryLink($category.id_category, $category.link_rewrite)|escape:'htmlall':'UTF-8'}" class="cat-link" title="">{$category.name|escape:'htmlall':'UTF-8'}</a>
		<a href="{$link->getCategoryLink($category.id_category, $category.link_rewrite)|escape:'htmlall':'UTF-8'}" title="">
			{if $category.id_image}
			<img src="{$link->getCatImageLink($category.link_rewrite, $category.id_image, 'medium_default')}" alt="{$category.name|escape:'htmlall':'UTF-8'}">
			{else}
			<img src="{$img_cat_dir}default-medium_default.jpg" width="{$mediumSize.width}" height="{$mediumSize.height}" alt="{$category.name|escape:'htmlall':'UTF-8'}">
			{/if}
		</a>
		{if isset($category.childcategory)}
		<ul>
			{foreach from=$category.childcategory item=child}
		  	<li>- <a href="{$link->getCategoryLink($child.id_category, $child.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{$child.name|escape:'htmlall':'UTF-8'}">{$child.name|escape:'htmlall':'UTF-8'}</a></li>
		  	{/foreach}
		</ul>
			{if $category.count_cild > $max_child_cats}
			<a href="{$link->getCategoryLink($category.id_category, $category.link_rewrite)|escape:'htmlall':'UTF-8'}" title="{l s='See more' mod='blockhomecategories'}" class="more">{l s='See more' mod='blockhomecategories'}</a>
			{/if}
		{/if}		
	</div>
	{/foreach}

</div>
{/if}