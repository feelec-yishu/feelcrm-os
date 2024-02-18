/**
 * Created by navyy on 2018.01.05.
 */
document.writeln("<!-- Root element of PhotoSwipe. Must have class pswp. -->");
document.writeln("<div class=\"pswp\" tabindex=\"-1\" role=\"dialog\" aria-hidden=\"true\">");
document.writeln("");
document.writeln("    <!-- Background of PhotoSwipe.");
document.writeln("         It\'s a separate element as animating opacity is faster than rgba(). -->");
document.writeln("    <div class=\"pswp__bg\"><\/div>");
document.writeln("");
document.writeln("    <!-- Slides wrapper with overflow:hidden. -->");
document.writeln("    <div class=\"pswp__scroll-wrap\">");
document.writeln("");
document.writeln("        <!-- Container that holds slides.");
document.writeln("            PhotoSwipe keeps only 3 of them in the DOM to save memory.");
document.writeln("            Don\'t modify these 3 pswp__item elements, data is added later on. -->");
document.writeln("        <div class=\"pswp__container\">");
document.writeln("            <div class=\"pswp__item\"><\/div>");
document.writeln("            <div class=\"pswp__item\"><\/div>");
document.writeln("            <div class=\"pswp__item\"><\/div>");
document.writeln("        <\/div>");
document.writeln("");
document.writeln("        <!-- Default (PhotoSwipeUI_Default) interface on top of sliding area. Can be changed. -->");
document.writeln("        <div class=\"pswp__ui pswp__ui--hidden\">");
document.writeln("");
document.writeln("            <div class=\"pswp__top-bar\">");
document.writeln("");
document.writeln("                <!--  Controls are self-explanatory. Order can be changed. -->");
document.writeln("");
document.writeln("                <div class=\"pswp__counter\"><\/div>");
document.writeln("");
document.writeln("                <button class=\"pswp__button pswp__button--close\" title=\"Close (Esc)\"><\/button>");
// document.writeln("");
// document.writeln("                <button class=\"pswp__button pswp__button--share\" title=\"Share\"><\/button>");
// document.writeln("");
// document.writeln("                <button class=\"pswp__button pswp__button--fs\" title=\"Toggle fullscreen\"><\/button>");
document.writeln("");
document.writeln("                <button class=\"pswp__button pswp__button--zoom\" title=\"Zoom in\/out\"><\/button>");
document.writeln("");
document.writeln("                <!-- Preloader demo http:\/\/codepen.io\/dimsemenov\/pen\/yyBWoR -->");
document.writeln("                <!-- element will get class pswp__preloader--active when preloader is running -->");
document.writeln("                <div class=\"pswp__preloader\">");
document.writeln("                    <div class=\"pswp__preloader__icn\">");
document.writeln("                        <div class=\"pswp__preloader__cut\">");
document.writeln("                            <div class=\"pswp__preloader__donut\"><\/div>");
document.writeln("                        <\/div>");
document.writeln("                    <\/div>");
document.writeln("                <\/div>");
document.writeln("            <\/div>");
document.writeln("");
document.writeln("            <div class=\"pswp__share-modal pswp__share-modal--hidden pswp__single-tap\">");
document.writeln("                <div class=\"pswp__share-tooltip\"><\/div>");
document.writeln("            <\/div>");
document.writeln("");
document.writeln("            <button class=\"pswp__button pswp__button--arrow--left\" title=\"Previous (arrow left)\">");
document.writeln("            <\/button>");
document.writeln("");
document.writeln("            <button class=\"pswp__button pswp__button--arrow--right\" title=\"Next (arrow right)\">");
document.writeln("            <\/button>");
document.writeln("");
document.writeln("            <div class=\"pswp__caption\">");
document.writeln("                <div class=\"pswp__caption__center\"><\/div>");
document.writeln("            <\/div>");
document.writeln("");
document.writeln("        <\/div>");
document.writeln("");
document.writeln("    <\/div>");
document.writeln("");
document.writeln("<\/div>");


var openPhotoSwipe = function(no,id)
{
    var pswpElement = document.querySelectorAll('.pswp')[0];

    // build items array
    var items = [];

    var aDiv = document.getElementById(id);

    var getItems = function()
    {
        if (aDiv.hasChildNodes())
        {
            $("#"+id).find('img').each(function()
            {
                var item = {
                    src:$(this).attr('src'),
                    w: $(this).context.naturalWidth,
                    h: $(this).context.naturalHeight
                };

                items.push(item);
            });
        }
    };

    getItems();

    // define options (if needed)
    var options = {
        // history & focus options are disabled on CodePen
        history: false,
        focus: false,
        index: no,
        showAnimationDuration: 0,
        hideAnimationDuration: 0
    };

    var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);

    gallery.init();
};

var openVisitorPhoto = function(no,id)
{
    var pswpElement = document.querySelectorAll('.pswp')[0];

    // build items array
    var items = [];

    var aDiv = document.getElementById(id);

    var getItems = function()
    {
        if (aDiv.hasChildNodes())
        {
            $("#"+id).find('img').each(function()
            {
                var item = {
                    src:$(this).attr('src'),
                    w: $(this).context.naturalWidth,
                    h: $(this).context.naturalHeight
                };

                items.push(item);
            });
        }
    };

    getItems();

    // define options (if needed)
    var options = {
        // history & focus options are disabled on CodePen
        history: false,
        focus: false,
        index: no,
        showAnimationDuration: 0,
        hideAnimationDuration: 0
    };

    var gallery = new PhotoSwipe(pswpElement, PhotoSwipeUI_Default, items, options);

    gallery.init();
};