Serialized Replace
==================

Replace all occurences in variable with replacement:

* variable can be serialized any times (even 0)
* variable's components can be also serialized any times (keys and values in array)
* support of regular expression replace and normal string replace
* objects aren't supported

## Example ##
```php
require_once('SerializedReplace.php');

$serialized = serialize(
serialize(
  array(
    serialize('cool index') => serialize(
      array(
        'this is long string' => serialize('long long long'),
        29 => array('just long string')
      )
    )
  )
)
);
$sr = new SerializedReplace($serialized);

$pattern = '#long((\s)(string))?#';
$replacement = '\3\2short';
$sr->replace($pattern, $replacement);
$result1 = $sr->get();

$pattern = 'cool ';
$replacement = '';
$sr->replace($pattern, $replacement, false);
$result2 = $sr->get();

$pattern = 29;
$replacement = 36;
$sr->replace($pattern, $replacement, false);
$result3 = $sr->get();
```

### Result ###
```php
$serialized = 's:143:"a:1:{s:18:"s:10:"cool index";";s:102:"a:2:{s:19:"this is long string";s:22:"s:14:"long long long";";i:29;a:1:{i:0;s:16:"just long string";}}";}";';

$result1 = 's:148:"a:1:{s:18:"s:10:"cool index";";s:107:"a:2:{s:20:"this is string short";s:25:"s:17:"short short short";";i:29;a:1:{i:0;s:17:"just string short";}}";}";';

$result2 = 's:142:"a:1:{s:12:"s:5:"index";";s:107:"a:2:{s:20:"this is string short";s:25:"s:17:"short short short";";i:29;a:1:{i:0;s:17:"just string short";}}";}";';

$result3 = 's:142:"a:1:{s:12:"s:5:"index";";s:107:"a:2:{s:20:"this is string short";s:25:"s:17:"short short short";";i:36;a:1:{i:0;s:17:"just string short";}}";}";';
```
