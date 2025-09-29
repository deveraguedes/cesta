// JS for beneficiario modal: moved from inline script
// Requires jQuery and Inputmask (loaded before this file)

(function($){
  $(function(){
    const $cpf = $('#cpf');
    const $nis = $('#nis');
    const $form = $('#formBeneficiario');
    const $submitBtn = $form.find('button[type="submit"]');
    const $alertEl = $('#alertBeneficiario');

    // Init Inputmask for live CPF formatting while typing
    try {
      // Prefer jQuery adapter when available
      if ($.fn && $.fn.inputmask) {
        $cpf.inputmask({ mask: '999.999.999-99', removeMaskOnSubmit: true, clearIncomplete: true });
      // Fallback to global Inputmask if present (no jQuery adapter)
      } else if (window.Inputmask) {
        try { Inputmask({ mask: '999.999.999-99', removeMaskOnSubmit: true, clearIncomplete: true }).mask($cpf.get(0)); } catch(e) {}
      }
    } catch (e) {
      // silently ignore init errors and fall back to regex-based handling below
    }

    // CPF validation
    function verificarCPF(cpf) {
      cpf = (cpf || '').replace(/\D+/g, '');
      if (cpf.length !== 11) return false;
      let soma = 0; let resto;
      if (/^(\d)\1{10}$/.test(cpf)) return false;
      for (let i = 1; i <= 9; i++) soma += parseInt(cpf.substring(i-1, i), 10) * (11 - i);
      resto = (soma * 10) % 11;
      if ((resto === 10) || (resto === 11)) resto = 0;
      if (resto !== parseInt(cpf.substring(9, 10), 10)) return false;
      soma = 0;
      for (let i = 1; i <= 10; i++) soma += parseInt(cpf.substring(i-1, i), 10) * (12 - i);
      resto = (soma * 10) % 11;
      if ((resto === 10) || (resto === 11)) resto = 0;
      if (resto !== parseInt(cpf.substring(10, 11), 10)) return false;
      return true;
    }

    // Lightweight PIS validator
    function verificarPIS(pis) {
      if (!pis) return false;
      pis = pis.replace(/[^\d]/g, '');
      if (pis.length !== 11) return false;
      const weights = [3,2,9,8,7,6,5,4,3,2];
      let sum = 0;
      for (let i = 0; i < 10; i++) sum += parseInt(pis.charAt(i), 10) * weights[i];
      let resto = sum % 11;
      let dig = 11 - resto;
      if (dig === 10 || dig === 11) dig = 0;
      return dig === parseInt(pis.charAt(10), 10);
    }

    // Inline helpers
    let lastInline = { field: null, value: null, message: null };
    function showInlineError(fieldId, message, value = null) {
      const el = document.getElementById(fieldId);
      if (!el) return;
      el.classList.add('is-invalid');
      let feedback = el.nextElementSibling;
      if (!feedback || !feedback.classList.contains('invalid-feedback')) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        el.parentNode.appendChild(feedback);
      }
      feedback.textContent = message;
      lastInline.field = fieldId; lastInline.value = value != null ? String(value).trim() : (el.value || '').trim(); lastInline.message = message;
    }
    function clearInlineError(fieldId, force = false) {
      const el = document.getElementById(fieldId);
      if (!el) return;
      const current = (el.value || '').trim();
      if (!force && lastInline.field === fieldId && lastInline.value === current) return;
      el.classList.remove('is-invalid');
      const feedback = el.parentNode.querySelector('.invalid-feedback'); if (feedback) feedback.remove();
      if (lastInline.field === fieldId) lastInline = { field: null, value: null, message: null };
    }

    // Modal alert helpers
    let lastAlert = { field: null, value: null };
    function showModalAlert(message, type = 'danger', field = null, value = null) {
      if ($alertEl.length) {
        $alertEl.html('<button type="button" class="close" data-dismiss="alert" aria-label="Fechar"><span aria-hidden="true">&times;</span></button>' + message);
        $alertEl.removeClass('d-none alert-danger alert-warning alert-info alert-success').addClass('alert alert-' + type);
      }
      lastAlert.field = field; lastAlert.value = value != null ? String(value).trim() : null;
      try { const firstAlert = document.querySelector('.modal .alert[role="alert"]'); if (firstAlert) { firstAlert.setAttribute('tabindex','-1'); firstAlert.focus(); } } catch(e){}
    }
    function clearModalAlert() {
      if ($alertEl.length) { $alertEl.html(''); $alertEl.addClass('d-none').removeClass('alert alert-danger alert-warning alert-info alert-success'); }
      lastAlert.field = null; lastAlert.value = null;
    }

    function disableSubmit(disabled) { try { $submitBtn.prop('disabled', !!disabled); if (disabled) $submitBtn.addClass('disabled'); else $submitBtn.removeClass('disabled'); } catch(e){} }

    // helper to get unmasked cpf
    function getCpfDigits() {
      try {
        if ($.fn && $.fn.inputmask) {
          const unmasked = $cpf.inputmask('unmaskedvalue');
          if (unmasked !== undefined && unmasked !== null) return String(unmasked).trim();
        } else if (window.Inputmask && typeof Inputmask.unmask === 'function') {
          try { const unm = Inputmask.unmask($cpf.val() || '', { mask: '999.999.999-99' }); if (unm !== undefined && unm !== null) return String(unm).trim(); } catch(e){}
        }
      } catch (e) {
        // fallthrough to regex fallback
      }
      // Last resort: strip non-digits
      return ($cpf.val() || '').replace(/\D/g, '').trim();
    }

    async function checkExists(field) {
      const cpf = getCpfDigits();
      const nis = ($nis.val() || '').replace(/\D/g, '').trim();
      try {
        const res = await $.post('processamento/check_identifiers.php', { cpf, nis });
        const result = typeof res === 'string' ? JSON.parse(res) : res;
        if (result && result.exists) {
          const details = Array.isArray(result.details) ? result.details : (result.details ? [result.details] : []);
          let same = details.find(d => d && (d.field === field || d.field_name === field || d.name === field || d.campo === field));
          if (!same) {
            const payloadVal = field === 'cpf' ? cpf : nis;
            same = details.find(d => { try { if (!d) return false; if (d.cpf && String(d.cpf).trim() === String(cpf)) return true; if (d.nis && String(d.nis).trim() === String(nis)) return true; if (d.valor && String(d.valor).trim() === String(payloadVal)) return true; if (d.identificador && String(d.identificador).trim() === String(payloadVal)) return true; } catch(e){} return false; });
          }
          if (!same && details.length) same = details[0];
          if (same) {
            const payloadVal = field === 'cpf' ? cpf : nis;
            const msg = same.message || `${(same.field || field).toUpperCase()} já existe em ${same.table || same.tabela || 'registro'}${same.id ? ' (id=' + same.id + ')' : ''}`;
            const payload = field === 'cpf' ? cpf : nis;
            showInlineError(field, msg, payload);
            showModalAlert(msg, 'danger', field, payloadVal);
            disableSubmit(true);
            return false;
          }
        }
        clearInlineError(field);
        if (!lastAlert.field || lastAlert.field === field) clearModalAlert();
        disableSubmit(false);
        return true;
      } catch(e) { console.error('Erro ao verificar duplicados', e); disableSubmit(false); return true; }
    }

    async function verifica() {
      const nis = ($nis.val() || '').replace(/\D/g, '').trim();
      const cpf = getCpfDigits();
      if (!nis || !cpf) { alert('NIS e CPF devem ser informados!'); if (!nis) $nis.focus(); else $cpf.focus(); return false; }
      if (!verificarCPF(cpf)) { alert('CPF inválido!'); $cpf.focus(); return false; }
      const campos = ['nome','cod_bairro','endereco','cod_tipo']; const labels = ['Nome','Bairro','Endereço','Tipo de Beneficiário'];
      for (let i=0;i<campos.length;i++){ if (!$('#'+campos[i]).val()){ alert(labels[i]+' deve ser informado!'); $('#'+campos[i]).focus(); return false; } }
      const okCpf = await checkExists('cpf'); const okNis = await checkExists('nis'); if (!okCpf || !okNis) return false;
      // Inputmask is configured to remove mask on submit, but to be safe ensure digits-only
      try { $cpf.val( getCpfDigits() ); } catch(e){}
      $.post('processamento/processar_beneficiario.php', $form.serialize(), function(resp){ try{ const result = JSON.parse(resp); if (result.success) { alert('Beneficiário cadastrado com sucesso!'); $('#modalBeneficiario').modal('hide'); location.reload(); } else { alert(result.message || 'Erro ao cadastrar beneficiário'); } } catch { alert('Erro ao processar resposta do servidor'); } }).fail(function(){ alert('Erro ao enviar formulário'); });
      return false;
    }

    // debounce
    function debounce(fn, wait){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }; }

    const debouncedCheckCpf = debounce(()=>checkExists('cpf'), 350);
    const debouncedCheckNis = debounce(()=>checkExists('nis'), 350);

    // events
    $cpf.on('blur', function(){ const val = getCpfDigits(); if (val) { if (!verificarCPF(val)) { showInlineError('cpf','CPF inválido!', val); this.focus(); } else { clearInlineError('cpf'); clearModalAlert(); disableSubmit(false); checkExists('cpf'); } } else clearInlineError('cpf'); });

    $nis.on('blur', function(){ const val = ($nis.val()||'').replace(/\D/g,''); if (val){ if (verificarPIS(val)){ clearInlineError('nis'); clearModalAlert(); disableSubmit(false); checkExists('nis'); return; } if (verificarCPF(val) && !verificarPIS(val)){ showInlineError('nis','Este número parece ser um CPF, não um NIS. Verifique os campos.', val); showModalAlert('NIS informado parece ser um CPF. Verifique se os valores não estão trocados.', 'warning'); disableSubmit(true); this.focus(); return; } showInlineError('nis','NIS inválido ou com formato desconhecido. Confirme o número.', val); showModalAlert('NIS informado não passou na validação local; verifique se está correto.', 'warning'); disableSubmit(false); checkExists('nis'); } else clearInlineError('nis'); });

    $cpf.add($nis).on('keypress', function(e){ const charCode = e.which || e.keyCode; if (charCode !== 8 && charCode !== 9) { if (charCode < 48 || charCode > 57) e.preventDefault(); } });

    $cpf.on('input paste change', function(){ clearInlineError('cpf'); if (lastAlert.field === 'cpf'){ const current = getCpfDigits(); if (current !== lastAlert.value) clearModalAlert(); } debouncedCheckCpf(); });
    $nis.on('input paste change', function(){ clearInlineError('nis'); if (lastAlert.field === 'nis'){ const current = ($nis.val()||'').trim(); if (current !== lastAlert.value) clearModalAlert(); } debouncedCheckNis(); });

    $form.on('submit', function(e){ e.preventDefault(); verifica(); });
  });
})(jQuery);
