<?php
function render_stat_card(string $title, int $value, string $icon): void
{
    echo '<article class="card stat-card">';
    echo '<div><p class="helper-text">' . htmlspecialchars($title) . '</p><p class="metric">' . $value . '</p></div>';
    echo '<span class="stat-icon"><i class="bi ' . htmlspecialchars($icon) . '"></i></span>';
    echo '</article>';
}
