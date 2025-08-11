;(() => {
  "use strict";
  document.querySelectorAll(".lxform-form").forEach(function (e) {
    var r = e.querySelector(".g-recaptcha");
    e.addEventListener("submit", function (t) {
      t.preventDefault();
      var a = e.querySelector(".lxform-submit-btn");
      a.setAttribute("disabled", "disabled");
      var n = a.textContent;
      a.innerHTML =
        '<span class="lxform-loader-box">\n            <span class="lxform-loader-icon"></span>\n            <span>Loading ...</span>\n        </span>';
      var o = e.querySelectorAll(".lxf-message");
      o.length > 0 &&
        o.forEach(function (e) {
          e.remove();
        });
      var s = e.querySelectorAll(".form-err");
      s.length > 0 &&
        s.forEach(function (e) {
          e.remove();
        }),
        e.querySelectorAll(".lxform-field-warp").forEach(function (e) {
          e.classList.remove("wlxf-validation-error");
        });
      var c = new FormData(e);
      c.append("action", "lxf_form_submit"),
        e
          .querySelectorAll('.lxform-checkbox-wrap input[type="checkbox"]')
          .forEach(function (e) {
            var r = e.getAttribute("name"),
              t = e.checked;
            c.append(r, t ? "Checked" : "-");
          }),
        e.querySelectorAll('input[type="file"]').forEach(function (e) {
          var r = e.getAttribute("name");
          c.append(r, e.files[0]);
        }),
        fetch(lxformData.ajax_url, { method: "POST", body: c })
          .then(function (e) {
            a.innerHTML = n;
            return e.json();
          })
            .then(function (t) {
                a.removeAttribute("disabled");
              if ((a.removeAttribute("disabled"), t))
                if (t.success) {
                  const msg = t?.data?.message ?? 'Submitted';
                  console.log('Form submission success:', t.data);

                  var n = t.data;
                  const mailFailed = n.message.includes('');
            
                  "1" == leadxforms_data.has_license
                    ? "none" === n.redirect
                      ? e.insertAdjacentHTML(
                          "beforeend",
                          `<div class="lxf-message ${mailFailed ? 'lxf-message-warning' : 'lxf-message-success'}">\n${n.message}\n</div>`
                        )
                      : (window.location = n.redirect)
                    : e.insertAdjacentHTML(
                        "beforeend",
                        `<div class="lxf-message ${mailFailed ? 'lxf-message-warning' : 'lxf-message-success'}">\n${n.message}\n</div>`
                      );
            
                  e.reset();
                  "undefined" != typeof grecaptcha && r && grecaptcha.reset(r);
                } else {
                  var o = t.data;
                  e.insertAdjacentHTML(
                    "beforeend",
                    '<div class="lxf-message lxf-message-warning">\n' + o.message + '\n</div>'
                  );
                  var s = o.errors;
                  for (var c in s) {
                    var l = e.querySelector('[data-name="' + c + '"]');
                    l.classList.add("wlxf-validation-error");
                    l.insertAdjacentHTML(
                      "beforeend",
                      '<div class="form-err">' + s[c][0] + '</div>'
                    );
                  }
                }
            })
          .catch(function (e) {
                a.removeAttribute("disabled");
                a.innerHTML = n;
                console.error(e);
          });
    });
  });
})();