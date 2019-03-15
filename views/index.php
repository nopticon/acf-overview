<?php namespace acf_overview; ?>

<style>
.overview-striped > tbody > tr > td:nth-child(2n+1) {
   background-color: #EEE;
}
.overview-striped.widefat {
    width: auto
}
.acf-overview .navigate {
    border: 1px solid #ddd;
    padding: 5px;
    line-height: 25px;
}
.acf-overview .navigate a {
    white-space: no-wrap;
}
</style>

<div class="wrap acf-overview">
    <h1><?php echo esc_html($title); ?></h1>

    <h3><?php _e('Navigate', 'acf-overview'); ?></h3>

    <div class="navigate">
        <?php if ( $v5_notice ) {
            echo $v5_notice;
        } ?>

        <?php foreach ($groups as $i => $group) { ?>
            <?php if ($i) { echo '&nbsp;&nbsp;&nbsp;'; } ?>
            <a href="#<?= acf_overview()->anchor($group['title']); ?>"><?php echo $group['title']; ?></a>
        <?php } ?>
    </div>

    <?php foreach ($groups as $i => $group) { ?>
        <a name="<?= acf_overview()->anchor($group['title']); ?>"></a>

        <?php if ($i) { echo '<br />'; } ?>

        <h2><?= $group['title']; ?></h2>

        <?php acf_overview()->table( $group['fields'] ); ?>
    <?php } ?>
</div>
