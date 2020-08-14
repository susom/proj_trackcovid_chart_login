<?php

namespace Stanford\ChartLogin\ChartLogin;

/** @var ChartLogin $this */

try {
    if (!$this->verifyCookie('login')) {
        ?>
        <link rel="stylesheet" href="<?php echo $this->getUrl('asset/css/authentication.css', true, true) ?>">
        <script src="<?php echo $this->getUrl('asset/js/authentication.js', true, true) ?>"></script>
        <script>
            CHART = {
                endpoint: "<?php echo $this->getUrl('ajax/index.php', true, true) ?>",
                recordId: "<?php echo $this->getRecordId() ?>"
            }
        </script>

        <div class="container">

        </div>
        <?php
    } else {
        // todo get link from another em.
        $this->redirectToScheduler();
    }
} catch (\Exception $e) {

}

