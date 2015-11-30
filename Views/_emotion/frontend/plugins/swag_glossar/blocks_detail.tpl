{block name="frontend_index_header_javascript_jquery" append}
	<script type="text/javascript">
		(function ($) {
			$.fn.tipTip = {};
		})(jQuery);
	</script>
	<script type="text/javascript" src="{link file="frontend/_resources/javascript/tiptip.js"}"></script>
	<script type="text/javascript">
		(function ($) {
			$(document).ready(function () {
				/** Remove the old tiptip holder */
				$('#tiptip_holder').remove();

				/** Initialize the tiptip plugin */
				$('.tiptip').tipTip({
					'width': {$configs.width},
					'defaultPosition': '{$configs.position}'
				})
			});
		})(jQuery);
	</script>
{/block}

{block name="frontend_index_header_css_screen" append}
	<link rel="stylesheet" media="screen, projection" href="{link file="frontend/_resources/css/tiptip.css"}"/>
{/block}