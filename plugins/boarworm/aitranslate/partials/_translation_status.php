<?php
    $sites = \Site::listSites();
    $model = $model ?? $record;
    // Cache site versions for the current record
    $otherSiteIds = $model->newOtherSiteQuery()->pluck('site_id')->all();
?>
<div style="display: flex; gap: 4px; flex-wrap: wrap;">
    <?php foreach ($sites as $site): ?>
        <?php $exists = in_array($site->id, $otherSiteIds); ?>
        <span 
            title="<?= e($site->name) ?>: <?= $exists ? 'Translated' : 'Missing' ?>" 
            style="
                display: inline-block; 
                padding: 1px 4px; 
                border-radius: 3px; 
                font-size: 10px; 
                line-height: 1.4;
                font-weight: 600;
                background: <?= $exists ? '#dcfce7' : '#f3f4f6' ?>; 
                color: <?= $exists ? '#15803d' : '#9ca3af' ?>;
                border: 1px solid <?= $exists ? '#bbf7d0' : '#e5e7eb' ?>;
                text-transform: uppercase;
                cursor: default;
            "
        >
            <?= e($site->code) ?>
        </span>
    <?php endforeach; ?>
</div>
