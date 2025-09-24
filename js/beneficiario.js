// Funções de validação
function somenteNumeros(e) {
    var charCode = e.charCode ? e.charCode : e.keyCode;
    if (charCode != 8 && charCode != 9) {
        if (charCode < 48 || charCode > 57) {
            return false;
        }
    }
    return true;
}

function verificarCPF(cpf) {
    cpf = cpf.replace(/[^\d]/g, '');
    
    if (cpf.length != 11 || /^(\d)\1{10}$/.test(cpf)) {
        alert("CPF inválido!");
        document.getElementById("cpf").value = "";
        document.getElementById("cpf").focus();
        return false;
    }

    let sum = 0;
    let rest;

    for (let i = 1; i <= 9; i++) {
        sum = sum + parseInt(cpf.substring(i-1, i)) * (11 - i);
    }

    rest = (sum * 10) % 11;
    if ((rest == 10) || (rest == 11)) rest = 0;
    if (rest != parseInt(cpf.substring(9, 10))) {
        alert("CPF inválido!");
        document.getElementById("cpf").value = "";
        document.getElementById("cpf").focus();
        return false;
    }

    sum = 0;
    for (let i = 1; i <= 10; i++) {
        sum = sum + parseInt(cpf.substring(i-1, i)) * (12 - i);
    }

    rest = (sum * 10) % 11;
    if ((rest == 10) || (rest == 11)) rest = 0;
    if (rest != parseInt(cpf.substring(10, 11))) {
        alert("CPF inválido!");
        document.getElementById("cpf").value = "";
        document.getElementById("cpf").focus();
        return false;
    }

    return true;
}

function verificarCPFNIS(valor) {
    if (valor.length > 0) {
        let isNIS = document.getElementById("nis").value.length > 0;
        let isCPF = document.getElementById("cpf").value.length > 0;
        
        if (isNIS && isCPF) {
            alert("Preencha apenas NIS ou CPF, não ambos!");
            if (isNIS) {
                document.getElementById("cpf").value = "";
            } else {
                document.getElementById("nis").value = "";
            }
            return false;
        }
    }
    return true;
}

function verifica() {
    // Verifica se pelo menos NIS ou CPF foi preenchido
    if (!document.getElementById("nis").value && !document.getElementById("cpf").value) {
        alert("NIS ou CPF deve ser informado!");
        document.getElementById("nis").focus();
        return false;
    }

    // Valida campos obrigatórios
    let campos = ["nome", "cod_bairro", "endereco", "cod_tipo"];
    let labels = ["Nome", "Bairro", "Endereço", "Tipo de Beneficiário"];

    for (let i = 0; i < campos.length; i++) {
        let campo = document.getElementById(campos[i]);
        if (!campo.value) {
            alert(labels[i] + " deve ser informado!");
            campo.focus();
            return false;
        }
    }

    // Se todas as validações passaram, envia via AJAX
    $.ajax({
        url: 'processamento/processar_beneficiario.php',
        method: 'POST',
        data: $('#formBeneficiario').serialize(),
        success: function(response) {
            try {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Beneficiário cadastrado com sucesso!');
                    $('#modalBeneficiario').modal('hide');
                    window.location.reload();
                } else {
                    alert(result.message || 'Erro ao cadastrar beneficiário');
                }
            } catch(e) {
                alert('Erro ao processar resposta do servidor');
            }
        },
        error: function() {
            alert('Erro ao enviar formulário');
        }
    });
    return false;
}

// Configuração do modal
$(document).ready(function() {
    console.log('Script inicializado');

    $('#modalBeneficiario').on('show.bs.modal', function (e) {
        console.log('Modal está abrindo');
        var modal = $(this);
        
        $.ajax({
            url: 'forms/inserir_beneficiario.php',
            method: 'GET',
            success: function(response) {
                console.log('Formulário carregado com sucesso');
                modal.find('.modal-content').html(response);
            },
            error: function(xhr, status, error) {
                console.error('Erro ao carregar formulário:', error);
                console.error('Status:', status);
                console.error('Resposta:', xhr.responseText);
                alert('Erro ao carregar o formulário. Verifique o console para mais detalhes.');
                modal.modal('hide');
            }
        });
    });

    // Limpa o conteúdo do modal quando ele for fechado
    $('#modalBeneficiario').on('hidden.bs.modal', function () {
        console.log('Modal fechado - Limpando conteúdo');
        $(this).find('.modal-content').html('');
    });
});