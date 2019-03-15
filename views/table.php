<?php namespace acf_overview; ?>

<table width="100%" class="wp-list-table widefat striped table-main">
    <thead>
        <tr>
        <?php

        foreach ( $columns as $name => $label ) {
            echo '<th>' . $label . '</th>';
        }

        ?>
        </tr>
    </thead>

    <?php foreach ( $fields as $field ) { ?>
    <tr>
        <?php

        foreach ( $columns as $name => $label ) {
            $value = $field[$name] ? $field[$name] : '&nbsp;';

            echo '<td>' . $value . '</td>';
        }

        ?>
    </tr>

    <?php if ( !empty($field['children']) ) { ?>
    <tr>
        <td><span style="color: blue;">&raquo;</span></td>
        <td colspan="<?php echo acf_overview()->colspan( $columns ) ?>">
            <?php acf_overview()->table( $field['children'] ); ?>
        </td>
    </tr>
    <?php } } ?>

</table>
