import app from 'flarum/admin/app';

app.initializers.add('jerrymalmstrom-paste-image', () => {
  app.extensionData.for('jerrymalmstrom-paste-image').registerSetting({
    setting: 'jerrymalmstrom-paste-image.max_file_size',
    type: 'number',
    label: app.translator.trans('jerrymalmstrom-paste-image.admin.max_file_size_label'),
    help: app.translator.trans('jerrymalmstrom-paste-image.admin.max_file_size_help'),
    min: 1,
  }).registerSetting({
    setting: 'jerrymalmstrom-paste-image.allowed_types',
    type: 'text',
    label: app.translator.trans('jerrymalmstrom-paste-image.admin.allowed_types_label'),
    help: app.translator.trans('jerrymalmstrom-paste-image.admin.allowed_types_help'),
  });
});
