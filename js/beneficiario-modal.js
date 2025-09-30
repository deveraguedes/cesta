// JS for beneficiario modal: moved from inline script
// Requires jQuery and Inputmask (loaded before this file)

(function($){
  $(function(){
    const $cpf = $('#cpf');
    const $nis = $('#nis');
    // Target the nearest form containing the CPF field to work for both Inserir/Alterar
    const $form = $cpf.closest('form');
    const $submitBtn = $form.find('button[type="submit"], input[type="submit"], input[type="button"][onclick*="verifica"]');
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
    let lastInline = { field: null, value: null, message: null, type: null };
    function showInlineError(fieldId, message, value = null, type = 'validation') {
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
      lastInline.field = fieldId; lastInline.value = value != null ? String(value).trim() : (el.value || '').trim(); lastInline.message = message; lastInline.type = type;
    }
    function clearInlineError(fieldId, force = false) {
      const el = document.getElementById(fieldId);
      if (!el) return;
      const current = (el.value || '').trim();
      // Do not clear validation-origin errors unless forced or value changed
      if (!force) {
        if (lastInline.field === fieldId) {
          if (lastInline.type === 'validation' && lastInline.value === current) return;
        }
      }
      el.classList.remove('is-invalid');
      const feedback = el.parentNode.querySelector('.invalid-feedback'); if (feedback) feedback.remove();
      if (lastInline.field === fieldId) lastInline = { field: null, value: null, message: null, type: null };
    }

    // Modal alert helpers
    let lastAlert = { field: null, value: null };
    function showModalAlert(message, type = 'danger', field = null, value = null) {
      // Disable top alert - only use inline validation
      // We're keeping the lastAlert state tracking for compatibility
      lastAlert.field = field; lastAlert.value = value != null ? String(value).trim() : null;
    }
    function clearModalAlert() {
      // Just clear the tracking state
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
          // Prefer a matching detail by field -> by value
          let same = details.find(d => d && (d.field === field || d.field_name === field));
          if (!same) {
            const payloadVal = field === 'cpf' ? cpf : nis;
            same = details.find(d => {
              try {
                if (!d) return false;
                if (d.cpf && String(d.cpf).replace(/\D/g,'') === String(cpf)) return true;
                if (d.nis && String(d.nis).replace(/\D/g,'') === String(nis)) return true;
                if (d.valor && String(d.valor).replace(/\D/g,'') === String(payloadVal)) return true;
                if (d.identificador && String(d.identificador).replace(/\D/g,'') === String(payloadVal)) return true;
              } catch(e){}
              return false;
            });
          }
          if (!same && details.length) same = details[0];
          if (same) {
            const payloadVal = field === 'cpf' ? cpf : nis;
            // Build a user-friendly message. Server may include unidade_cod and message
            let msg = same.message || `${(same.field || field).toUpperCase()} já existe`;
            if (same.unidade_cod || same.cod_unidade || same.unidade) {
              const u = same.unidade || same.unit_name || same.unidade_nome || null;
              const cod = same.unidade_cod || same.cod_unidade || null;
              msg = `${(field === 'cpf' ? 'CPF' : 'NIS')} já existe na unidade ${u || cod}`;
            }
            showInlineError(field, msg, payloadVal, 'duplicate');
            showModalAlert(msg, 'danger', field, payloadVal);
            disableSubmit(true);
            return false;
          }
        }
        // Only clear inline error if the last inline message originated from 'duplicate'
        if (lastInline.field === field) {
          if (lastInline.type === 'duplicate') {
            clearInlineError(field);
          }
        } else {
          clearInlineError(field);
        }
        if (!lastAlert.field || lastAlert.field === field) clearModalAlert();
        disableSubmit(false);
        return true;
      } catch(e) { console.error('Erro ao verificar duplicados', e); disableSubmit(false); return true; }
    }

    function verifica() {
      const nis = ($nis.val() || '').replace(/\D/g, '').trim();
      const cpf = getCpfDigits();
      // Require at least one identifier (NIS or CPF). If provided, each must be valid.
      if (!cpf && !nis) {
        showInlineError('cpf', 'Informe NIS ou CPF. Pelo menos um é obrigatório.', '', 'validation');
        // also hint on NIS field without marking invalid twice
        $nis.length && $nis.focus();
        disableSubmit(false);
        return false;
      }
      if (cpf && !verificarCPF(cpf)) {
        showInlineError('cpf', 'CPF inválido!', cpf, 'validation');
        $cpf.focus();
        disableSubmit(false);
        return false;
      }
      if (nis) {
        if (verificarCPF(nis) && !verificarPIS(nis)) {
          showInlineError('nis','Este número parece ser um CPF, não um NIS. Verifique os campos.', nis, 'validation');
          disableSubmit(false);
          $nis.focus();
          return false;
        }
        if (!verificarPIS(nis)) {
          showInlineError('nis','NIS inválido ou com formato desconhecido. Confirme o número.', nis, 'validation');
          disableSubmit(false);
          $nis.focus();
          return false;
        }
      }
      const campos = ['nome','cod_bairro','endereco','cod_tipo'];
      const labels = ['Nome','Bairro','Endereço','Tipo de Beneficiário'];
      for (let i = 0; i < campos.length; i++) {
        const $el = $('#'+campos[i]);
        if (!$el.val()) {
          // Show inline required-field error in a consistent way
          showInlineError(campos[i], labels[i]+' deve ser informado!');
          $el.focus();
          disableSubmit(false);
          return false;
        }
      }

      // Mostrar indicador de carregamento
      disableSubmit(true);
      const originalSubmitHtml = $submitBtn.html();
      $submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Salvando...');

      // Garantir CPF apenas dígitos antes de enviar
      try { if ($cpf.length) $cpf.val(getCpfDigits()); } catch(e){}

      // Enviar formulário via AJAX para receber JSON e mostrar mensagem na página
      const actionUrl = ($form.attr('action') || 'usuarios/processamento/processar_beneficiario.php');
      $.ajax({
        url: actionUrl,
        method: 'POST',
        dataType: 'json',
        data: $form.serialize()
      }).done(function(resp){
        // Restaurar botão e habilitar novamente
        $submitBtn.html(originalSubmitHtml);
        disableSubmit(false);

        if (resp && resp.success) {
          const isUpdate = !!$form.find('[name="MM_action"][value="2"]').length || !!($form.find('[name="cod_beneficiario"]').val() || '').trim();
          const successMsg = resp.message || (isUpdate ? 'Beneficiário atualizado com sucesso' : 'Beneficiário cadastrado com sucesso');
          try { if (typeof showPageAlert === 'function') showPageAlert('success', successMsg); } catch(e){}

          // Fechar modal (se dentro de um modal) e atualizar a lista
          const $modal = $form.closest('.modal');
          if ($modal && $modal.length) {
            try { $modal.modal('hide'); } catch(e){}
          }
          setTimeout(function(){ try { location.reload(); } catch(e){} }, 1200);
        } else {
          const msg = (resp && resp.message) ? resp.message : 'Erro ao processar a solicitação.';
          try { if (typeof showPageAlert === 'function') showPageAlert('danger', msg); } catch(e){}
          // manter modal aberto para correções; erros inline já são tratados acima
        }
      }).fail(function(xhr, status){
        $submitBtn.html(originalSubmitHtml);
        disableSubmit(false);
        const msg = 'Erro na requisição: ' + (xhr && xhr.responseText ? xhr.responseText : status);
        try { if (typeof showPageAlert === 'function') showPageAlert('danger', msg); } catch(e){}
      });
      return true;
    }

    // debounce
    function debounce(fn, wait){ let t; return function(...args){ clearTimeout(t); t = setTimeout(()=>fn.apply(this,args), wait); }; }

    // Increase debounce slightly to avoid flicker during typing/paste
    const debouncedCheckCpf = debounce(()=>checkExists('cpf'), 700);
    const debouncedCheckNis = debounce(()=>checkExists('nis'), 700);

  // Slightly delayed check for blur events to avoid flicker
  const blurCheckCpf = debounce(()=>checkExists('cpf'), 800);
  const blurCheckNis = debounce(()=>checkExists('nis'), 800);

    // events
    // Replace blur-triggered validation with debounced validation on input
    // This shows the 'CPF inválido' message only after the value has been stable for a bit,
    // avoiding quick flickers when the user types or pastes.
    const debouncedValidateCpf = debounce(function(){
      const val = getCpfDigits();
      if (val) {
        if (!verificarCPF(val)) {
          showInlineError('cpf', 'CPF inválido!', val, 'validation');
        } else {
          clearInlineError('cpf', true);
        }
      } else {
        clearInlineError('cpf', true);
      }
    }, 700);

    $nis.on('blur', function(){ const val = ($nis.val()||'').replace(/\D/g,''); if (val){ if (verificarPIS(val)){ clearInlineError('nis'); clearModalAlert(); disableSubmit(false); checkExists('nis'); return; } if (verificarCPF(val) && !verificarPIS(val)){ showInlineError('nis','Este número parece ser um CPF, não um NIS. Verifique os campos.', val); showModalAlert('NIS informado parece ser um CPF. Verifique se os valores não estão trocados.', 'warning'); disableSubmit(true); this.focus(); return; } showInlineError('nis','NIS inválido ou com formato desconhecido. Confirme o número.', val); showModalAlert('NIS informado não passou na validação local; verifique se está correto.', 'warning'); disableSubmit(false); checkExists('nis'); } else clearInlineError('nis'); });

    $cpf.add($nis).on('keypress', function(e){ const charCode = e.which || e.keyCode; if (charCode !== 8 && charCode !== 9) { if (charCode < 48 || charCode > 57) e.preventDefault(); } });

    $cpf.on('input paste change', function(){
      // Do not clear inline error immediately; keep it visible until value validates
      if (lastAlert.field === 'cpf'){
        const current = getCpfDigits();
        if (current !== lastAlert.value) clearModalAlert();
      }
      debouncedCheckCpf();
      debouncedValidateCpf();
    });
    $nis.on('input paste change', function(){ clearInlineError('nis'); if (lastAlert.field === 'nis'){ const current = ($nis.val()||'').trim(); if (current !== lastAlert.value) clearModalAlert(); } debouncedCheckNis(); });

    if ($form && $form.length) {
      $form.on('submit', function(e){ e.preventDefault(); verifica(); });
    }
  });
})(jQuery);
