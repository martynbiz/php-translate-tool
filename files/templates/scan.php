<?php

use Cattlog\Output;

?>
Checking...
<?php if ($fileSystem->fileExists($destFile)): ?>
    <?php echo $destFile . PHP_EOL; ?>
<?php else: ?>
    <?php echo Output::highlight($destFile) . PHP_EOL; ?>
<?php endif; ?>

<?php if (count($keysToAdd)): ?>
The following new keys were found:
<?php foreach ($keysToAdd as $value): ?>
    <?php echo Output::_($value, Output::BG_GREEN) . PHP_EOL; ?>
<?php endforeach; ?>

<?php endif; if (count($keysToRemove)): ?>
The following keys are obsolete:
<?php foreach ($keysToRemove as $value): ?>
    <?php echo Output::_($value, Output::BG_RED) . PHP_EOL; ?>
<?php endforeach; ?>

<?php endif; ?>
