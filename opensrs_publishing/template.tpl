{if $customfn == 'OSRSPLoginTo'}
<div class="wbox">
	<div class="wbox_header">{$lang.OSRSPLoginTo}</div>
	<div class="wbox_content">
	    <div style="width:120px; margin: auto">
	    <form action="{$OSRSPLoginTo.hostname}/login/" method="post" target="_blank" >
	        <input type="hidden" name="username" value="{$OSRSPLoginTo.username}" />
	        <input type="hidden" name="password" value="{$OSRSPLoginTo.password}" />
	        <input type="submit" value="{$lang.OSRSPLoginTo}" />
	    </form>
	    </div>
	</div>
</div>
{elseif $customfn == 'LoginInfo'}
 <div class="wbox">
  	<div class="wbox_header">{$lang.logindetails|capitalize}</div>

	<div class="wbox_content">
		<table width="100%" cellspacing="0" cellpadding="0" border="0" class="checker">
			<tr class="even">
			    <td width="160" align="right">URL</td>
			    <td><a href="{$LoginInfo.hostname}" target="_blank">{$LoginInfo.hostname}</a></td>
			</tr>
		    <tr>
		        <td width="160" align="right">{$lang.username}</td>
		        <td>{$LoginInfo.username}</td>
			</tr>
		    <tr class="even">
		        <td width="160" align="right">{$lang.password}</td>
		        <td>{$LoginInfo.password}</td>
		    </tr>
		</table>
	</div>
</div>
{/if}