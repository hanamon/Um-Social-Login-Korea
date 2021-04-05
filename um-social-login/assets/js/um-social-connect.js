jQuery(document).ready(function() {
   // Hide overflows
   if (jQuery(".um-social-login-overlay").length) {
      jQuery("body,html").css("overflow", "hidden");
   }

   var sso_current_avatar = "";

   // Change user avatar
   jQuery(document.body).on("click", "a.um-social-login-avatar-change", function() {
      var provider = jQuery(this).data("provider");
      var user_id = jQuery('input[type="hidden"][name="user_id"]').val();
      var profile_photo = jQuery(".um-profile-photo-img img");

      var avatar_image = jQuery(this).find("img");

      wp.ajax.send("um_social_login_change_photo", {
         data: {
            provider: provider,
            user_id: user_id,
            nonce: um_scripts.nonce
         },
         success: function(d) {
            if (typeof d.source !== "undefined" && d.source != "") {
               profile_photo.attr("src", d.source);

               sso_current_avatar = d.source;

               jQuery("a.um-dropdown-hide").trigger("click");
            }
         },
         error: function(e) {
            console.log(e);
         }
      });
   });

   // Swap social avatar to profile photo for preview
   jQuery(document.body)
      .on("mouseenter", "a.um-social-login-avatar-change img", function() {
         var sso_avatar = jQuery(this).attr("src");
         if (!sso_current_avatar) {
            sso_current_avatar = jQuery(".um-header .um-profile-photo img.um-avatar.um-avatar-uploaded").attr("src");
         }
         jQuery(".um-header .um-profile-photo img.um-avatar.um-avatar-uploaded").attr("src", sso_avatar);
      })
      .on("mouseleave", "a.um-social-login-avatar-change img", function() {
         jQuery(".um-header .um-profile-photo img.um-avatar.um-avatar-uploaded").attr("src", sso_current_avatar);
      });

   // Submit one-step process
   var form = jQuery(".um-social-login-wrap form input[type=hidden][name='_um_social_login_one_step']").parent("form");
   var show_flash_screen = jQuery(".um-social-login-wrap form input[type=hidden][name='_um_sso_show_flash_screen']").val();

   if (show_flash_screen !== "") {
      if (show_flash_screen == 1) {
         setTimeout(function() {
            if (form.length) {
               form.submit();
            }
         }, 4000);
      } else if (show_flash_screen == 0) {
         if (form.length) {
            form.submit();
         }
      }
   }
});

/**
 * Resize overlay
 */
function um_social_login_popup() {
   var overlay = jQuery(".um-social-login-overlay");
   var wrap = jQuery(".um-social-login-wrap");

   if (overlay.length) {
      jQuery(".um-social-login-wrap .um").css({
         "max-height": overlay.height() - 80 + "px"
      });

      var p_top = (overlay.height() - wrap.innerHeight()) / 2;
      wrap.animate({
         top: p_top + "px"
      });
   }
}

/**
 * Open new window for OAuthentication
 * @param  string url
 * @param  string windowTitle
 * @param  string windowSettings
 * @return boolean false
 */
function um_social_login_oauth_window(url, windowTitle, windowSettings) {
   window.open(url, "authWindow", "width=1048,height=690,scrollbars=yes");

   return false;
}

jQuery(window).on( 'load', function() {
   um_social_login_popup();
});

jQuery(window).resize(function() {
   um_social_login_popup();
});

jQuery(document).ready(function() {
   if (jQuery(".um-shortcode-social").length > 0) {
      var current_url = window.location.href;
      um_social_login_createCookie("um_sso_return_url", current_url, 1);
   } else {
      um_social_login_eraseCookie("um_sso_return_url");
   }
});

/**
 * Set Cookie
 * @param string name
 * @param string value
 * @param string days
 */
function um_social_login_createCookie(name, value, days) {
   var expires;

   if (days) {
      var date = new Date();
      date.setTime(date.getTime() + days * 24 * 60 * 60 * 1000);
      expires = "; expires=" + date.toGMTString();
   } else {
      expires = "";
   }
   document.cookie = encodeURIComponent(name) + "=" + encodeURIComponent(value) + expires + "; path=/";
}

/**
 * Read Cookie
 * @param string name
 */
function um_social_login_readCookie(name) {
   var nameEQ = encodeURIComponent(name) + "=";
   var ca = document.cookie.split(";");
   for (var i = 0; i < ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) === " ") c = c.substring(1, c.length);
      if (c.indexOf(nameEQ) === 0) return decodeURIComponent(c.substring(nameEQ.length, c.length));
   }
   return null;
}

/**
 * Delete Cookie
 * @param string name
 */
function um_social_login_eraseCookie(name) {
   um_social_login_createCookie(name, "", -1);
}
