import '../main.js';
import Axios from 'axios';
import Cookies from 'js-cookie';
import 'jquery-confirm';

$(document).ready(() => {

    let _busy = false

    setupInviteMemberModal();
    setupMemberPermissionsModal();
    setupManagedAccountDelete();

    function setupInviteMemberModal() {
        let spinner = $('#modal-invite-account-manager-spinner');
        let form = $('#modal-invite-account-manager-form');

        $('.modal-invite-account-manager-close').click(e => {
            if (_busy)
                return;
            $('#modal-invite-account-manager').modal('hide');
        });

        form.submit(e => {
            if (_busy)
                return false;

            _busy = true;
            spinner.show();
            return true;
        });

        $('.member-invite-remove>button').click(e => {
            let invite_id = $(event.currentTarget).data('invite');
            let form = $('.member-invite-remove>form[data-invite=' + invite_id + ']');
            confirmAction(
                'Do you really want to remove this invite ?',
                'red', 'far far-exclamation-triangle',
                () => {
                    form.submit();
                }
            );            
        });

        $('.member-invite-resend>button').click(e => {
            let invite_id = $(event.currentTarget).data('invite');
            let form = $('.member-invite-resend>form[data-invite=' + invite_id + ']');
            confirmAction(
                'Do you really want to resend this invite ?',
                'orange', 'far far-exclamation-triangle',
                () => {
                    form.submit();
                }
            );            
        });
    }

    function setupMemberPermissionsModal() {

        let spinner = $('#modal-edit-account-manager-spinner');
        let form = $('#modal-edit-account-manager-form');

        $('.modal-edit-account-manager-close').click(e => {
            if (_busy)
                return;
            $('#modal-edit-account-manager').modal('hide');
        });

        $('.account-member-edit-permissions').click(e => {
            let member_id = $(e.currentTarget).data('member');
            $('#member-permissions-' + member_id).find('span[data-permission').each((e, v) => {
                let field = $(v).data('permission');
                let value = $(v).data('value');
                form.find('input[name="' + field + '"]').prop('checked', value);
            });

            form.find('input[name=member]').val(member_id);
            $('#modal-edit-account-manager').modal('show');
        });

        form.submit(e => {
            if (_busy)
                return false;

            _busy = true;
            spinner.show();
            return true;
        });

        $('.account-member-remove>button').click(e => {
            let member_id = $(event.currentTarget).data('member');
            let form = $('.account-member-remove>form[data-member=' + member_id + ']');
            confirmAction(
                'Do you really want to remove this member ?',
                'red', 'far far-exclamation-triangle',
                () => {
                    form.submit();
                }
            );            
        });
    }

    function setupManagedAccountDelete() {
        $('.managed-account-remove>button').click(e => {
            let account_id = $(event.currentTarget).data('account');
            let form = $('.managed-account-remove>form[data-account=' +account_id + ']');
            confirmAction(
                'Do you really want to be removed from this account ?',
                'red', 'far far-exclamation-triangle',
                () => {
                    form.submit();
                }
            );            
        });
    }    

    function confirmAction(msg, color, icon, callback) {
        if (_busy)
            return;
        _busy = true;
    
        $.confirm({
            title: 'Are you sure ?',
            content: msg,
            icon: icon,
            type: color,
            typeAnimated: true,
            buttons: {
                no: function () {
                    _busy = false;
                    this.close();
                },
                confirm:  {
                    text: 'Yes',
                    btnClass: 'btn-' + color,
                    action: function () {
                        _busy = false;
                        callback();
                    }
                }
            }
        });
    };
});