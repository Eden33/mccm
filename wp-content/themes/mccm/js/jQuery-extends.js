/*
 * As mentioned in this post http://www.ericmmartin.com/5-tips-for-using-jquery-with-wordpress/
 * Query comes with noConflict option set to true, so we have to go on in non conflict mode 
 * as well as in s
 */
jQuery(function ($) {
	jQuery.fn.extend({
		  scrollTo : function(speed, easing) {
		    return this.each(function() {
		      var targetOffset = $(this).offset().top;
		      $('html,body').animate({scrollTop: targetOffset}, speed, easing);
		    });
		  }
	});
});