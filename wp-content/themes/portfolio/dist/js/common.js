import"slick-slider/slick/slick.css";import"slick-slider/slick/slick-theme.css";import $ from"jquery";import"slick-slider";import"bootstrap";import WOW from"wow.js";(o=>{"use strict";console.log("Welcome to my Portfolio ~ Jason"),o(".section--featured-posts__slider").slick({arrows:!0,dots:!0,infinite:!0,speed:300,slidesToShow:4,slidesToScroll:4,responsive:[{breakpoint:1024,settings:{slidesToShow:2,slidesToScroll:2,infinite:!0,dots:!0}},{breakpoint:600,settings:{slidesToShow:2,slidesToScroll:2}},{breakpoint:480,settings:{slidesToShow:1,slidesToScroll:1}}]}),o(window).scroll((function(){o(window).scrollTop()>=50?o(".header").addClass("fixed-top"):o(".header").removeClass("fixed-top")})),o("#offcanvasPrimary a").click((function(){o(".offcanvas").offcanvas("hide")}));const i=o(".component--accordion-and-media"),s=window.matchMedia("(min-width: 768px)");i.length&&i.each(((i,t)=>{const e=o(t).find("#accordion-content"),n=o(t).find("#accordion-thumbnail-desktop"),a=e.find(".accordion-collapse.show"),c=e.find(".accordion-button"),l=o=>{const i=o.find(".accordion-thumbnail");n.attr("src",i.attr("src")),n.attr("alt",i.attr("alt")),i.hide()};a.length&&s.matches&&l(a),c.each(((i,e)=>{const n=o(e).data("bs-target"),a=o(t).find(n);o(e).on("click",(()=>{s.matches&&l(a)}))})),window.onresize=()=>{a.length&&s.matches&&l(a)}}));const t=o(".wpcf7-form");t.length&&t.each(((i,s)=>{o(s).find("label.visually-hidden").each(((i,t)=>{const e=o(t).attr("for");o(s).find("span #"+e).attr("aria-label",o(t).text())}))}));new WOW({boxClass:"wow",animateClass:"animated",offset:0,mobile:!1,live:!0}).init()})(jQuery);