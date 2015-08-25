<?php

use Cattlog\Output;

?>
The following new keys were found:
<?php foreach ($keysToAdd as $value): ?>
    <?php echo Output::_($value, Output::BG_GREEN) . PHP_EOL; ?>
<?php endforeach; ?>
The following keys are obsolete:
<?php foreach ($keysToRemove as $value): ?>
    <?php echo Output::_($value, Output::BG_RED) . PHP_EOL; ?>
<?php endforeach; ?>
