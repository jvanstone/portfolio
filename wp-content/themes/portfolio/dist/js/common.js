(s=>{"use strict";s(".section--featured-posts__slider").slick({arrows:!0,dots:!0,infinite:!0,speed:300,slidesToShow:4,slidesToScroll:4,responsive:[{breakpoint:1024,settings:{slidesToShow:2,slidesToScroll:2,infinite:!0,dots:!0}},{breakpoint:600,settings:{slidesToShow:2,slidesToScroll:2}},{breakpoint:480,settings:{slidesToShow:1,slidesToScroll:1}}]}),s(window).scroll((function(){s(window).scrollTop()>=50?s(".header").addClass("fixed-top"):s(".header").removeClass("fixed-top")})),s("#offcanvasPrimary a").click((function(){s(".offcanvas").offcanvas("hide")}));const i=s(".section--clients"),o=window.matchMedia("(min-width: 768px)");i.length&&i.each(((i,t)=>{const e=s(t).find("#accordion-clients"),n=s(t).find("#client-thumbnail-desktop"),c=e.find(".accordion-collapse.show"),a=e.find(".accordion-button"),l=s=>{const i=s.find(".client-thumbnail");n.attr("src",i.attr("src")),n.attr("alt",i.attr("alt")),i.hide()};c.length&&o.matches&&l(c),a.each(((i,e)=>{const n=s(e).data("bs-target"),c=s(t).find(n);s(e).on("click",(()=>{o.matches&&l(c)}))})),window.onresize=()=>{c.length&&o.matches&&l(c)}}))})(jQuery);