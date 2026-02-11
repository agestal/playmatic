"use strict";

(function () {
    const table = document.getElementById("kt_table_users");

    if (!table) {
        return;
    }

    const baseToolbar = document.querySelector('[data-kt-user-table-toolbar="base"]');
    const selectedToolbar = document.querySelector('[data-kt-user-table-toolbar="selected"]');
    const selectedCount = document.querySelector('[data-kt-user-table-select="selected_count"]');
    const bulkDeleteButton = document.querySelector('[data-kt-user-table-select="delete_selected"]');
    const bulkDeleteInputs = document.getElementById("bulkDeleteInputs");
    const bulkDeleteForm = document.getElementById("bulkDeleteForm");
    const searchInput = document.querySelector('[data-kt-user-table-filter="search"]');
    const searchForm = document.getElementById("usersSearchForm");
    const exportButton = document.querySelector('[data-kt-users-modal-action="submit"]');

    const rowCheckboxes = () => Array.from(table.querySelectorAll(".row-checkbox"));

    const updateToolbarState = () => {
        const selected = rowCheckboxes().filter((checkbox) => checkbox.checked);

        if (!baseToolbar || !selectedToolbar || !selectedCount) {
            return;
        }

        if (selected.length > 0) {
            selectedCount.textContent = String(selected.length);
            baseToolbar.classList.add("d-none");
            selectedToolbar.classList.remove("d-none");
        } else {
            selectedCount.textContent = "0";
            selectedToolbar.classList.add("d-none");
            baseToolbar.classList.remove("d-none");
        }
    };

    const attachRowDeleteActions = () => {
        table.querySelectorAll('[data-kt-users-table-filter="delete_row"]').forEach((button) => {
            if (button.dataset.bound === "true") {
                return;
            }

            button.dataset.bound = "true";

            button.addEventListener("click", function (event) {
                event.preventDefault();

                const membershipId = button.getAttribute("data-membership-id");
                const userName = button.getAttribute("data-user-name") || "user";
                const form = document.getElementById(`delete-membership-${membershipId}`);

                if (!form) {
                    return;
                }

                Swal.fire({
                    text: `Are you sure you want to delete ${userName}?`,
                    icon: "warning",
                    showCancelButton: true,
                    buttonsStyling: false,
                    confirmButtonText: "Yes, delete!",
                    cancelButtonText: "No, cancel",
                    customClass: {
                        confirmButton: "btn fw-bold btn-danger",
                        cancelButton: "btn fw-bold btn-active-light-primary"
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    };

    const attachHeaderCheckboxAction = () => {
        const master = table.querySelector('[data-kt-check="true"]');

        if (!master || master.dataset.bound === "true") {
            return;
        }

        master.dataset.bound = "true";

        master.addEventListener("change", function () {
            rowCheckboxes().forEach((checkbox) => {
                checkbox.checked = master.checked;
            });

            updateToolbarState();
        });
    };

    const attachRowCheckboxes = () => {
        rowCheckboxes().forEach((checkbox) => {
            if (checkbox.dataset.bound === "true") {
                return;
            }

            checkbox.dataset.bound = "true";
            checkbox.addEventListener("change", updateToolbarState);
        });
    };

    const attachBulkDeleteAction = () => {
        if (!bulkDeleteButton || !bulkDeleteForm || !bulkDeleteInputs) {
            return;
        }

        bulkDeleteButton.addEventListener("click", function () {
            const selected = rowCheckboxes().filter((checkbox) => checkbox.checked).map((checkbox) => checkbox.value);

            if (selected.length === 0) {
                return;
            }

            Swal.fire({
                text: "Are you sure you want to delete selected users?",
                icon: "warning",
                showCancelButton: true,
                buttonsStyling: false,
                confirmButtonText: "Yes, delete!",
                cancelButtonText: "No, cancel",
                customClass: {
                    confirmButton: "btn fw-bold btn-danger",
                    cancelButton: "btn fw-bold btn-active-light-primary"
                }
            }).then((result) => {
                if (!result.isConfirmed) {
                    return;
                }

                bulkDeleteInputs.innerHTML = "";

                selected.forEach((membershipId) => {
                    const input = document.createElement("input");
                    input.type = "hidden";
                    input.name = "tenant_user_ids[]";
                    input.value = membershipId;
                    bulkDeleteInputs.appendChild(input);
                });

                bulkDeleteForm.submit();
            });
        });
    };

    const attachSearchSubmit = () => {
        if (!searchInput || !searchForm) {
            return;
        }

        let searchTimer = null;

        searchInput.addEventListener("keyup", function () {
            if (searchTimer) {
                clearTimeout(searchTimer);
            }

            searchTimer = setTimeout(() => {
                searchForm.submit();
            }, 450);
        });
    };

    const attachExportAction = () => {
        if (!exportButton) {
            return;
        }

        exportButton.addEventListener("click", function () {
            const rows = Array.from(table.querySelectorAll("tbody tr"))
                .filter((row) => row.querySelectorAll("td").length > 1)
                .map((row) => {
                    const cols = row.querySelectorAll("td");
                    const userText = cols[1]?.innerText?.replace(/\s+/g, " ").trim() || "";
                    const roleText = cols[2]?.innerText?.trim() || "";
                    const lastLoginText = cols[3]?.innerText?.trim() || "";
                    const twoStepText = cols[4]?.innerText?.trim() || "";
                    const joinedText = cols[5]?.innerText?.trim() || "";

                    return [userText, roleText, lastLoginText, twoStepText, joinedText];
                });

            if (rows.length === 0) {
                return;
            }

            const header = ["User", "Role", "Last login", "Two-step", "Joined Date"];
            const csv = [header, ...rows]
                .map((line) => line.map((value) => `"${String(value).replace(/"/g, '""')}"`).join(","))
                .join("\n");

            const blob = new Blob([csv], { type: "text/csv;charset=utf-8;" });
            const link = document.createElement("a");
            link.href = URL.createObjectURL(blob);
            link.download = "users-export.csv";
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            const exportModalElement = document.getElementById("kt_modal_export_users");
            const exportModal = bootstrap.Modal.getInstance(exportModalElement);
            exportModal?.hide();
        });
    };

    const init = () => {
        attachHeaderCheckboxAction();
        attachRowCheckboxes();
        attachBulkDeleteAction();
        attachSearchSubmit();
        attachRowDeleteActions();
        attachExportAction();
        updateToolbarState();
    };

    init();
})();
