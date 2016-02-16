<?php

use MartynBiz\Translate\Tool\Output;

?>
<?php foreach ($emptyKeys as $key => $value): ?>
<?php echo Output::warning($key) . PHP_EOL; ?>
<?php endforeach; ?>
<?php foreach ($nonEmptyKeys as $key => $value): ?>
<?php echo $key; ?>          <?php echo Output::crop($value) . PHP_EOL; ?>
<?php endforeach; ?>
