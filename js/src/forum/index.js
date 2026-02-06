import app from 'flarum/forum/app';
import addPasteHandler from './addPasteHandler';

app.initializers.add('jerrymalmstrom-paste-image', () => {
  addPasteHandler();
});
