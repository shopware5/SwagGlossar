{extends file='frontend/index/index.tpl'}

{block name='frontend_index_content'}
	<div class="glossar--content">
        <h2 class="glossar--headline">Glossar</h2>
        <div class="glossar--content-box">
            <div class="glossar--group">
                {foreach $alphabet as $letter}
                {* If the index is 3 *}
                {math equation="a % 3" a=$letter@index assign="iteration"}
                {if $iteration == 0 && !$letter@first}
            </div>
            <div class="glossar--group">
                {/if}
                <div class="glossar--column">
                    <h2 class="glossar--column-headline">{$letter}</h2>
                    <ul>
                        {foreach $results.$letter as $result}
                            <li class="glossar--entry">
                                <a class="glossar--column-keyword" style="background-color: {$configs.color}">{$result.key}</a>
                                <div class="glossar--column-content">
                                    {$result.value}
                                </div>
                            </li>
                        {/foreach}
                    </ul>
                </div>
                {/foreach}
            </div>
        </div>
    </div>
{/block}
