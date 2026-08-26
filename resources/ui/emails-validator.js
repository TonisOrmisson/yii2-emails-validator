(() => {
    class EmailsValidator extends HTMLElement {
        connectedCallback() {
            if (this.form) return;
            this.apiBase = this.getAttribute('api-base') || '';
            this.csrfToken = this.getAttribute('csrf-token') || '';
            this.renderForm();
        }

        renderForm() {
            this.form = document.createElement('form');
            this.form.addEventListener('submit', (event) => this.submit(event));

            const label = document.createElement('label');
            label.setAttribute('for', 'emails-validator-input');
            label.textContent = 'E-mail addresses';
            this.form.append(label);

            this.textInput = document.createElement('textarea');
            this.textInput.id = 'emails-validator-input';
            this.textInput.name = 'textInput';
            this.textInput.rows = 10;
            this.form.append(this.textInput);

            this.displayOnlyProblems = this.checkbox('displayOnlyProblems', 'Display only e-mails with problems', true);
            this.checkDNS = this.checkbox('checkDNS', 'Perform DNS check', true);
            this.checkSpoof = this.checkbox('checkSpoof', 'Perform spoofing check', true);

            const submit = document.createElement('button');
            submit.type = 'submit';
            submit.textContent = 'Validate';
            this.form.append(submit);

            this.status = document.createElement('p');
            this.status.setAttribute('aria-live', 'polite');
            this.status.setAttribute('role', 'status');
            this.form.append(this.status);

            this.results = document.createElement('div');
            this.form.append(this.results);
            this.append(this.form);
        }

        checkbox(name, labelText, checked) {
            const wrapper = document.createElement('div');
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.name = name;
            input.id = `emails-validator-${name}`;
            input.checked = checked;
            const label = document.createElement('label');
            label.setAttribute('for', input.id);
            label.textContent = labelText;
            wrapper.append(input, label);
            this.form.append(wrapper);
            return input;
        }

        async submit(event) {
            event.preventDefault();
            const button = this.form.querySelector('button[type="submit"]');
            button.disabled = true;
            this.status.textContent = 'Checking e-mail addresses…';
            this.results.replaceChildren();
            try {
                const response = await fetch(this.apiBase, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': this.csrfToken
                    },
                    body: JSON.stringify({
                        textInput: this.textInput.value,
                        checkDNS: this.checkDNS.checked,
                        checkSpoof: this.checkSpoof.checked,
                        displayOnlyProblems: this.displayOnlyProblems.checked
                    })
                });
                const payload = await response.json();
                if (!response.ok) {
                    this.showErrors(payload.errors || {});
                    return;
                }
                this.status.textContent = `Checked ${payload.meta.total} address(es); ${payload.meta.failed} failed.`;
                this.showResults(payload.data || []);
            } catch (error) {
                this.status.textContent = 'Unable to validate the e-mail addresses.';
            } finally {
                button.disabled = false;
            }
        }

        showErrors(errors) {
            const message = document.createElement('p');
            message.setAttribute('aria-live', 'assertive');
            message.textContent = Object.values(errors).flat().join(' ');
            this.results.append(message);
            this.status.textContent = 'Validation request was rejected.';
        }

        showResults(results) {
            if (!results.length) {
                const empty = document.createElement('p');
                empty.textContent = 'No addresses have problems.';
                this.results.append(empty);
                return;
            }
            const table = document.createElement('table');
            const head = document.createElement('thead');
            const row = document.createElement('tr');
            ['Address', 'Needs trimming', 'Valid', 'RFC', 'RFC warnings', 'DNS', 'Spoof check'].forEach((text) => {
                const cell = document.createElement('th');
                cell.setAttribute('scope', 'col');
                cell.textContent = text;
                row.append(cell);
            });
            head.append(row);
            table.append(head);
            const body = document.createElement('tbody');
            results.forEach((result) => {
                const resultRow = document.createElement('tr');
                [result.address, result.needs_trimming, result.is_valid, result.is_valid_rfc,
                    result.is_no_rfc_warnings, result.is_valid_dns, result.is_valid_spoof_check].forEach((value) => {
                    const cell = document.createElement('td');
                    cell.textContent = typeof value === 'boolean' ? (value ? 'Yes' : 'No') : String(value ?? '');
                    resultRow.append(cell);
                });
                body.append(resultRow);
            });
            table.append(body);
            this.results.append(table);
        }
    }

    customElements.define('emails-validator', EmailsValidator);
})();
