$(function()
{
    $('form #desc').focus(function(){$(this).height($(this).closest('.row').height()-57);}).blur(function(){$(this).removeAttr('style')});
    $('#menu li').removeClass('active').find('[href*=' + v.mode + ']').parent().addClass('active');
});

$(function()
{
    /* move search button to left menu. */
    $('#bysearchTab').appendTo($('#menu').find('ul'));
    
    if(v.mode == 'bysearch')
    {
        $('#bysearchTab').addClass('active');
        ajaxGetSearchForm();
    }
    toggleSearch();
});
