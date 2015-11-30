{extends file='frontend/index/index.tpl'}

{block name="frontend_index_header_javascript_jquery" append}
	<script type="text/javascript">
		$(document).ready(function () {
			$('.glossar-content').hide();
			{* Add a class, so the first one changes it's style *}
			$('.glossar-content:first').show().parent().find('a').addClass('glossar-active');
			{* Creates a collapse-effect *}
			$('a.keyword').click(function () {
				$(this).toggleClass('glossar-active').parent().find('div').slideToggle('fast');
			});
		});
	</script>
{/block}

{block name="frontend_index_header_css_screen" append}
	<link type="text/css" href="{link file='frontend/_resources/css/glossar.css'}" media="screen" rel="stylesheet"/>
{/block}

{block name='frontend_index_content'}
	<div id="center" class="grid_16 first last glossar">
		<h2 class="headingbox_nobg">Glossar</h2>

		<div class="content_box">
			<div class="row">
				{foreach $alphabet as $letter}
				{* If the index is 3 *}
				{math equation="a % 3" a=$letter@index assign="iteration"}
				{if $iteration == 0 && !$letter@first}
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
			<div class="row">
				{/if}
				<div class="column">
					<h2 class="letter">{$letter}</h2>
					<ul>
						{foreach $results.$letter as $result}
							<li>
								<a class="keyword" style="background-color: {$configs.color}">{$result.key}</a>
								<div class="glossar-content">
									{$result.value}
								</div>
							</li>
						{/foreach}
					</ul>
				</div>
				{/foreach}
			</div>
			<div class="clear"></div>
		</div>
	</div>
	<div class="doublespace"></div>
{/block}
