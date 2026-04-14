@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const forms = document.querySelectorAll('.js-basic-validation-form, .js-inline-form');

            forms.forEach((form) => {
                form.addEventListener('submit', () => {
                    form.querySelectorAll('input[type="text"], textarea').forEach((field) => {
                        field.value = field.value.trim();
                    });
                });
            });

            document.querySelectorAll('.js-inline-edit-toggle').forEach((button) => {
                button.addEventListener('click', () => {
                    const form = document.getElementById(button.dataset.target);
                    const input = form.querySelector('.js-inline-input');

                    form.classList.remove('d-none');
                    input.focus();
                    input.select();
                });
            });

            document.querySelectorAll('.js-inline-cancel').forEach((button) => {
                button.addEventListener('click', () => {
                    const form = button.closest('.js-inline-form');
                    const input = form.querySelector('.js-inline-input');
                    const error = form.querySelector('.js-inline-error');

                    form.classList.add('d-none');
                    input.classList.remove('is-invalid');
                    error.classList.add('d-none');
                    error.textContent = '';
                });
            });

            document.querySelectorAll('.js-inline-form').forEach((form) => {
                form.addEventListener('submit', async (event) => {
                    event.preventDefault();

                    const input = form.querySelector('.js-inline-input');
                    const error = form.querySelector('.js-inline-error');
                    const wrapper = form.closest('.js-inline-wrapper');
                    const display = wrapper.querySelector('.js-inline-display');
                    const submitButton = form.querySelector('button[type="submit"]');
                    const originalLabel = submitButton.textContent;

                    input.value = input.value.trim();
                    input.classList.remove('is-invalid');
                    error.classList.add('d-none');
                    error.textContent = '';

                    if (input.value.length < 2) {
                        input.classList.add('is-invalid');
                        error.textContent = 'El nombre debe tener al menos 2 caracteres.';
                        error.classList.remove('d-none');
                        input.focus();
                        return;
                    }

                    submitButton.disabled = true;
                    submitButton.textContent = 'Guardando...';

                    try {
                        const response = await fetch(form.action, {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                            body: new FormData(form),
                        });

                        const payload = await response.json();

                        if (!response.ok) {
                            throw payload;
                        }

                        display.textContent = payload.data.nombre;
                        input.value = payload.data.nombre;
                        form.classList.add('d-none');
                    } catch (payload) {
                        const message = payload?.errors?.nombre?.[0] ?? payload?.message ?? 'No se ha podido actualizar el nombre.';
                        input.classList.add('is-invalid');
                        error.textContent = message;
                        error.classList.remove('d-none');
                        input.focus();
                    } finally {
                        submitButton.disabled = false;
                        submitButton.textContent = originalLabel;
                    }
                });
            });

            const deleteModal = document.getElementById('delete-confirmation-modal');

            if (deleteModal) {
                const deleteForm = deleteModal.querySelector('.js-delete-modal-form');
                const deleteMessage = deleteModal.querySelector('.js-delete-modal-message');

                document.querySelectorAll('.js-delete-button').forEach((button) => {
                    button.addEventListener('click', () => {
                        deleteForm.action = button.dataset.action;
                        deleteMessage.innerHTML = `${button.dataset.message} Esta acción no se puede deshacer.`;
                        window.jQuery(deleteModal).modal('show');
                    });
                });
            }
        });
    </script>
@endpush
