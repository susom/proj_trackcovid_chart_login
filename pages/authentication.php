<?php

namespace Stanford\TrackCovid\ProjChart;

/** @var ProjChart $this */

?>

<script>
    CHART = {
        endpoint: <?php echo json_encode($this->getUrl('ajax',true,true)) ?>
    }
</script>


<?php

