{strip}
{* Kategori vitrin paletini döndürür. Kullanım: {include file='./partials/pal.tpl' id=$idCategory idx=$index assign=_pal} *}
{assign var=_cvSt value=$cupcake.styles[$id]|default:[]}
{if $_cvSt.palette|default:'' != ''}
{$_cvSt.palette|escape}
{else}
{assign var=_cvDefaults value=['cilek','vanilya','fistik','yaban-mersini','karamel','lavanta','nane','limon']}
{$_cvDefaults[($idx|default:0) % 8]}
{/if}
{/strip}
