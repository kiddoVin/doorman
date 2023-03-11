import app from 'flarum/admin/app';
import Doorkey from './models/Doorkey';
import SettingsPage from './components/SettingsPage';

app.initializers.add('kiddo-doorman', () => {
  app.store.models.doorkeys = Doorkey;

  app.extensionData.for('kiddo-doorman').registerPage(SettingsPage);
});
