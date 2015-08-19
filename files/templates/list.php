<?php

use Cattlog\Output;

// don't filter files that don't exist, we want to show those too
$destFiles = $fileSystem->getDestFiles($lang);

?>Checking...
<?php foreach ($destFiles as $file): ?>
<?php if ($fileSystem->fileExists($file)): ?>
    <?php echo $file . PHP_EOL; ?>
<?php else: ?>
    <?php echo Output::warning($file) . PHP_EOL; ?>
<?php endif; ?>
<?php endforeach; ?>

<?php foreach ($emptyKeys as $key => $value): ?>
<?php echo Output::warning($key) . PHP_EOL; ?>
<?php endforeach; ?>
<?php foreach ($nonEmptyKeys as $key => $value): ?>
<?php echo $key; ?>          <?php echo Output::crop($value) . PHP_EOL; ?>
<?php endforeach; ?>
