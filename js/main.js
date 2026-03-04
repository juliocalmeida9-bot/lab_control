// ===============================
// CONTROL LAB - JS PROFISSIONAL
// ===============================

document.addEventListener("DOMContentLoaded", function () {

    // ===============================
    // SISTEMA DE NOTIFICAÇÃO
    // ===============================
    function showNotification(message, type = "success") {
        const notification = document.createElement("div");
        notification.classList.add("notification", type);
        notification.innerText = message;

        document.body.appendChild(notification);

        setTimeout(() => {
            notification.classList.add("show");
        }, 100);

        setTimeout(() => {
            notification.classList.remove("show");
            setTimeout(() => notification.remove(), 500);
        }, 3000);
    }

    // ===============================
    // CONFIRMAÇÃO ESTILIZADA
    // ===============================
    function customConfirm(message, callback) {
        const modal = document.createElement("div");
        modal.classList.add("modal");

        modal.innerHTML = `
            <div class="modal-content">
                <p>${message}</p>
                <div class="modal-buttons">
                    <button id="confirmYes">Sim</button>
                    <button id="confirmNo">Cancelar</button>
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        document.getElementById("confirmYes").onclick = function () {
            modal.remove();
            callback(true);
        };

        document.getElementById("confirmNo").onclick = function () {
            modal.remove();
            callback(false);
        };
    }

    // ===============================
    // CHECKLIST RETIRADA
    // ===============================
    const retiradaForm = document.querySelector("form[action='processar_retirada.php']");

    if (retiradaForm) {
        retiradaForm.addEventListener("submit", function (e) {
            e.preventDefault();

            const checkboxes = retiradaForm.querySelectorAll("input[type='checkbox']");
            let todosMarcados = true;

            checkboxes.forEach(cb => {
                if (!cb.checked) {
                    todosMarcados = false;
                }
            });

            if (!todosMarcados) {
                showNotification("Confirme todos os itens do checklist!", "error");
                return;
            }

            customConfirm("Confirmar retirada dos equipamentos?", function (confirmado) {
                if (confirmado) {
                    showLoader();
                    retiradaForm.submit();
                }
            });
        });
    }

    // ===============================
    // MODO ESCURO / CLARO
    // ===============================
    const toggleBtn = document.getElementById("toggleTheme");

    if (toggleBtn) {
        toggleBtn.addEventListener("click", function () {
            document.body.classList.toggle("light-mode");

            const mode = document.body.classList.contains("light-mode") ? "light" : "dark";
            localStorage.setItem("theme", mode);
        });
    }

    // Carregar tema salvo
    const savedTheme = localStorage.getItem("theme");
    if (savedTheme === "light") {
        document.body.classList.add("light-mode");
    }

    // ===============================
    // LOADER
    // ===============================
    function showLoader() {
        const loader = document.createElement("div");
        loader.classList.add("loader-overlay");
        loader.innerHTML = `<div class="spinner"></div>`;
        document.body.appendChild(loader);
    }

});