{strip}
{* Kategori vitrin paletini döndürür. Kullanım: {include file='./partials/pal.tpl' id=$idCategory idx=$index assign=_pal} *}
{assign var=_cvSt value=$antepli.styles[$id]|default:[]}
{if $_cvSt.palette|default:'' != ''}
{$_cvSt.palette|escape}
{else}
{assign var=_cvDefaults value=['susam','fistik','kakao','tereyagi','karamel','nane','visne','tahin']}
{$_cvDefaults[($idx|default:0) % 8]}
{/if}
{/strip}
