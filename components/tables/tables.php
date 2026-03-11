<?php
function render_table_tools(string $placeholder = 'Buscar na tabela...'): void
{
    echo '<div class="table-tools">';
    echo '<input type="search" class="table-search" placeholder="' . htmlspecialchars($placeholder) . '">';
    echo '<select class="table-page-size"><option>5</option><option selected>10</option><option>20</option><option>50</option></select>';
    echo '</div>';
}
