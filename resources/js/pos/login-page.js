export function loginPage() {
    return {
        selectedUserId: null,
        selectedUserName: '',
        pin: '',

        selectUser(id, name) {
            this.selectedUserId = id;
            this.selectedUserName = name;
            this.pin = '';
        },

        back() {
            this.selectedUserId = null;
            this.pin = '';
        },

        pressDigit(digit) {
            if (this.pin.length < 6) {
                this.pin += String(digit);
            }
        },

        backspace() {
            this.pin = this.pin.slice(0, -1);
        },

        get canSubmit() {
            return this.pin.length >= 4;
        },

        submit() {
            if (!this.canSubmit) return;
            this.$refs.pinField.value = this.pin;
            this.$refs.userField.value = this.selectedUserId;
            this.$refs.loginForm.submit();
        },
    };
}
