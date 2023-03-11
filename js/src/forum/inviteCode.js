import app from 'flarum/forum/app';
import { extend } from 'flarum/common/extend';

import SettingsPage from 'flarum/forum/components/SettingsPage';
import FieldSet from 'flarum/common/components/FieldSet';

export default function addInviteCode() {
  extend(SettingsPage.prototype, 'settingsItems', function (items) {
    items.add(
      'inviteCode',
      FieldSet.component(
        {
          label: app.translator.trans('inviteCode.forum.user.settings.showCode'),
          className: 'Settings-follow-tags',
        },
        [
          <div className="Form-group">
            <p>您的邀请连接是：http://invites.fun/register?code={this.user.id()}</p>
          </div>,
        ]
      )
    );
  });
}
