<table class="wp-list-table widefat overview-striped">
    <tbody>
        <tr>
            <?php

            foreach ( $list as $name => $value ) {
                echo '<td>' . $name . '</td><td>' . $value . '</td>';
            }

            ?>
        </tr>
    </tbody>
</table>
