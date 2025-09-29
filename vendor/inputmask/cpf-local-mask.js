(function(global){
  // Minimal local CPF mask shim to avoid CDN dependency.
  // Provides:
  // - $.fn.inputmask(options) initializer and 'unmaskedvalue' call
  // - window.Inputmask(options).mask(element)
  // - window.Inputmask.unmask(value, opts)
  (function($){
    function formatCpfDigits(digits){
      digits = (digits||'').replace(/\D/g,'').slice(0,11);
      if (digits.length <= 3) return digits;
      if (digits.length <= 6) return digits.slice(0,3) + '.' + digits.slice(3);
      if (digits.length <= 9) return digits.slice(0,3) + '.' + digits.slice(3,6) + '.' + digits.slice(6);
      return digits.slice(0,3) + '.' + digits.slice(3,6) + '.' + digits.slice(6,9) + '-' + digits.slice(9,11);
    }

    function attachMask(el){
      if (!el) return;
      // avoid attaching twice
      if (el.__cpfMaskAttached) return; el.__cpfMaskAttached = true;
      function applyFormat(){
        const start = el.selectionStart || 0;
        const before = el.value || '';
        const digits = (before || '').replace(/\D/g,'');
        const formatted = formatCpfDigits(digits);
        el.value = formatted;
        // try to restore cursor near the end (best-effort)
        try{ el.selectionStart = el.selectionEnd = Math.min(formatted.length, start + (formatted.length - before.length)); } catch(e){}
      }
      el.addEventListener('input', applyFormat);
      el.addEventListener('paste', function(){ setTimeout(applyFormat, 0); });
      // initial format
      applyFormat();
    }

    if (window.jQuery) {
      var $ = window.jQuery;
      if (!$.fn.inputmask) {
        $.fn.inputmask = function(opts){
          // If called as .inputmask('unmaskedvalue') return digits
          if (typeof opts === 'string' && opts === 'unmaskedvalue'){
            var el = this[0];
            return el ? (String(el.value||'').replace(/\D/g,'')) : '';
          }
          // initialize
          this.each(function(){ attachMask(this); });
          return this;
        };
      }
    }

    if (!window.Inputmask) {
      window.Inputmask = function(opts){
        return {
          mask: function(node){ try{ attachMask(node); } catch(e){} },
        };
      };
      window.Inputmask.unmask = function(val, opts){ return String(val||'').replace(/\D/g,'').slice(0,11); };
    }

  })(window.jQuery);
})(this);
