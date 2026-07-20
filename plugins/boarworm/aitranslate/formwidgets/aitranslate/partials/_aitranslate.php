<?php if ($this->previewMode): ?>

    <div class="form-control">
        <?= $value ?>
    </div>

<?php else: ?>
    <div class="translate-ai-wrapper">
        <div class="form-group span-left">
            <label class="form-label">Target Languages:</label>

            <div class="field-checkboxlist is-scrollable" data-control="checkboxlist" data-ignore-dirty>
                <div class="checkboxlist-controls">
                    <a href="javascript:;" class="backend-toolbar-button control-button" data-field-checkboxlist-all>
                        <i class="icon-check-multi"></i>
                        <span class="button-label"><?= e(trans('backend::lang.form.select_all')) ?></span>
                    </a>
                    <a href="javascript:;" class="backend-toolbar-button control-button" data-field-checkboxlist-none>
                        <i class="icon-eraser"></i>
                        <span class="button-label"><?= e(trans('backend::lang.form.select_none')) ?></span>
                    </a>
                </div>

                <div class="field-checkboxlist-inner">
                    <div class="field-checkboxlist-scrollable">
                        <div class="control-scrollbar" data-control="scrollbar">
                            <?php foreach ($targetSites as $site): ?>
                                <div class="form-check">
                                    <input type="checkbox" name="translate_sites[]" value="<?= $site->id ?>"
                                        id="site_<?= $site->id ?>" class="form-check-input">
                                    <label class="form-check-label" for="site_<?= $site->id ?>">
                                        <?= e($site->name) ?>
                                    </label>
                                </div>
                            <?php endforeach ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="pt-4 pl-4 form-group flex flex-layout-column gap-4 span-right" data-track-input="false">
            <div id="ai-translate-progress" class="mb-3" style="display:none;">
                <div id="ai-translate-warning" class="mb-2 text-warning"><strong><i class="icon-exclamation-triangle"></i> Translation in progress, please do not close this page</strong></div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                </div>
                <small class="text-muted" id="ai-translate-status">Preparing...</small>
            </div>
            
            <div class="">
                <div class="mb-2 text-muted">Create translations for empty sites only. Safe to run anytime.</div>
                <button type="button" class="btn btn-default" id="btn-translate-missing">
                    <i class="icon-plus"></i> Translate Missing
                </button>
            </div>
            <div class="">
                <div class="mb-2 text-muted">Update and overwrite existing translations for selected sites.</div>
                <button type="button" class="btn btn-outline-danger" id="btn-translate-force">
                    <i class="icon-refresh"></i> Force Update
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const wrapper = document.querySelector('.translate-ai-wrapper');
        if (!wrapper) return;

        async function translateQueue(mode) {
            const checkboxes = wrapper.querySelectorAll('input[name="translate_sites[]"]:checked');
            const siteIds = Array.from(checkboxes).map(cb => cb.value);

            if (siteIds.length === 0) {
                $.oc.flashMsg({text: 'Please select at least one language.', class: 'error'});
                return;
            }

            if (mode === 'update') {
                if (!confirm('Overwrite existing content?')) {
                    return;
                }
            }
            
            doTranslation(mode, siteIds);
        }

        async function doTranslation(mode, siteIds) {
            const wrapper = document.querySelector('.translate-ai-wrapper');
            const progressDiv = document.getElementById('ai-translate-progress');
            const warningDiv = document.getElementById('ai-translate-warning');
            const progressBar = progressDiv.querySelector('.progress-bar');
            const statusText = document.getElementById('ai-translate-status');
            const buttons = wrapper.querySelectorAll('button');

            // Reset UI
            progressDiv.style.display = 'block';
            warningDiv.className = 'mb-2 text-warning';
            warningDiv.innerHTML = '<strong><i class="icon-exclamation-triangle"></i> Translation in progress, please do not close this page</strong>';
            progressBar.style.width = '0%';
            progressBar.className = 'progress-bar'; // Reset color
            buttons.forEach(btn => btn.disabled = true);

            let completed = 0;
            const total = siteIds.length;

            // Save form once before translating
            statusText.textContent = 'Saving form...';
            let saveError = null;
            await new Promise((resolve) => {
                $.request('onSaveForm', {
                    form: 'form',
                    success: () => resolve(),
                    error: (xhr) => {
                        saveError = xhr.responseText || xhr.statusText || 'Save failed';
                        resolve();
                    }
                });
            });
            
            if (saveError) {
                $.oc.flashMsg({text: 'Failed to save form: ' + saveError, class: 'error'});
                buttons.forEach(btn => btn.disabled = false);
                return;
            }

            for (const siteId of siteIds) {
                statusText.textContent = `Translating ${completed + 1} of ${total}...`;

                await new Promise(resolve => {
                    $.request('onTranslateSingle', {
                        form: 'form',
                        data: { mode: mode, site_id: siteId },
                        success: (res) => {
                            if (res.success) {
                                $.oc.flashMsg({text: res.skipped ? `Skipped ${res.site}` : `Translated ${res.site}`, class: 'success'});
                            } else {
                                $.oc.flashMsg({text: `Failed ${res.site}: ${res.error}`, class: 'error'});
                            }
                            resolve();
                        },
                        error: () => {
                            $.oc.flashMsg({text: `Request failed for site ${siteId}`, class: 'error'});
                            resolve(); // Continue anyway
                        }
                    });
                });

                completed++;
                progressBar.style.width = (completed / total * 100) + '%';
            }

            buttons.forEach(btn => btn.disabled = false);
            statusText.textContent = 'All Done!';
            
            // Update to Success State
            warningDiv.className = 'mb-2 text-success';
            warningDiv.innerHTML = '<strong><i class="icon-check"></i> Translation Completed Successfully!</strong>';
            progressBar.className = 'progress-bar bg-success';
            
            // Reset change monitor to prevent "unsaved changes" warning
            $('form').trigger('unchange.oc.changeMonitor');
            
            // Do NOT hide the progress bar
        }

        document.getElementById('btn-translate-missing').addEventListener('click', () => translateQueue('create'));
        document.getElementById('btn-translate-force').addEventListener('click', () => translateQueue('update'));
    })();
    </script>
<?php endif ?>