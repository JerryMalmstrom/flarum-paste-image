import { extend } from 'flarum/common/extend';
import TextEditor from 'flarum/common/components/TextEditor';
import app from 'flarum/forum/app';

export default function addPasteHandler() {
  extend(TextEditor.prototype, 'oncreate', function (result, vnode) {
    const textarea = this.element.querySelector('textarea');
    if (!textarea) return;

    this.pasteImageHandler = (e) => {
      const items = e.clipboardData && e.clipboardData.items;
      if (!items) return;

      let imageItem = null;
      for (let i = 0; i < items.length; i++) {
        if (items[i].type.indexOf('image/') === 0) {
          imageItem = items[i];
          break;
        }
      }

      if (!imageItem) return;

      e.preventDefault();

      const file = imageItem.getAsFile();
      if (!file) return;

      const editor = this.attrs.composer.editor;
      const placeholder = '![uploading-' + Date.now() + ']()';
      editor.insertAtCursor(placeholder);

      textarea.classList.add('paste-image-uploading');

      const formData = new FormData();
      formData.append('paste-image', file);

      app
        .request({
          method: 'POST',
          url: app.forum.attribute('apiUrl') + '/paste-image/upload',
          serialize: (raw) => raw,
          body: formData,
        })
        .then((response) => {
          const markdown = '![](' + response.url + ')';
          const content = editor.getContents();
          editor.setContents(content.replace(placeholder, markdown));
          textarea.classList.remove('paste-image-uploading');
        })
        .catch(() => {
          const content = editor.getContents();
          editor.setContents(content.replace(placeholder, ''));
          textarea.classList.remove('paste-image-uploading');
          app.alerts.show(
            { type: 'error' },
            app.translator.trans('jerrymalmstrom-paste-image.forum.upload_error')
          );
        });
    };

    textarea.addEventListener('paste', this.pasteImageHandler);
  });

  extend(TextEditor.prototype, 'onremove', function () {
    if (this.pasteImageHandler) {
      const textarea = this.element && this.element.querySelector('textarea');
      if (textarea) {
        textarea.removeEventListener('paste', this.pasteImageHandler);
      }
    }
  });
}
