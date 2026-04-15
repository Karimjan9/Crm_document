<script>
window.packageTemplatesCatalog = @json(array_values($packageTemplates ?? []));

(function () {
    const packageTemplates = Array.isArray(window.packageTemplatesCatalog)
        ? window.packageTemplatesCatalog
        : [];

    if (!packageTemplates.length || typeof WizardManager === 'undefined' || typeof WizardController === 'undefined') {
        return;
    }

    const picker = document.getElementById('documentPackageBundlePicker');
    if (!picker) {
        return;
    }

    const packageMap = new Map(packageTemplates.map((item) => [Number(item.id), item]));
    const money = (value) => `${Number(value || 0).toLocaleString()} so'm`;
    const wait = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

    const normalizeMode = (value) => {
        const mode = String(value || '').trim();

        return mode === '' ? 'service' : mode;
    };

    const normalizeSelection = (value) => {
        const mode = String(value || '').trim();

        return mode === '' ? null : mode;
    };

    const normalizeInt = (value) => {
        if (value === null || value === undefined || value === '') {
            return null;
        }

        const normalized = Number(value);

        return Number.isFinite(normalized) && normalized > 0 ? normalized : null;
    };

    const normalizeAddons = (items) => {
        if (!Array.isArray(items)) {
            return [];
        }

        const normalized = items
            .map((item) => {
                const sourceType = item?.sourceType ?? item?.type ?? null;
                const id = Number(item?.id || 0);

                if (!sourceType || !id) {
                    return null;
                }

                return {
                    id,
                    sourceType: String(sourceType).trim(),
                };
            })
            .filter(Boolean);

        return normalized
            .filter((item, index, array) => array.findIndex((entry) => entry.id === item.id && entry.sourceType === item.sourceType) === index)
            .sort((left, right) => `${left.sourceType}:${left.id}`.localeCompare(`${right.sourceType}:${right.id}`));
    };

    const addonKey = (item) => `${item.sourceType}:${item.id}`;

    const normalizeComparableConfig = (config) => {
        const processMode = normalizeMode(config?.process_mode);
        const selectionMode = normalizeSelection(config?.selection_mode);

        return {
            document_type_id: normalizeInt(config?.document_type_id),
            service_id: normalizeInt(config?.service_id),
            process_mode: processMode,
            selection_mode: processMode === 'consul' ? selectionMode : null,
            direction_type_id: processMode === 'apostil' ? normalizeInt(config?.direction_type_id) : null,
            apostil_group1_id: processMode === 'apostil' ? normalizeInt(config?.apostil_group1_id) : null,
            apostil_group2_id: processMode === 'apostil' ? normalizeInt(config?.apostil_group2_id) : null,
            consul_id: processMode === 'consul' && ['consul', 'mixed'].includes(selectionMode)
                ? normalizeInt(config?.consul_id)
                : null,
            consulate_type_id: processMode === 'consul' && ['legalization', 'mixed'].includes(selectionMode)
                ? normalizeInt(config?.consulate_type_id)
                : null,
            selected_addons: normalizeAddons(config?.selected_addons),
        };
    };

    const sameConfig = (left, right) => {
        const normalizedLeft = normalizeComparableConfig(left);
        const normalizedRight = normalizeComparableConfig(right);

        const scalarFields = [
            'document_type_id',
            'service_id',
            'process_mode',
            'selection_mode',
            'direction_type_id',
            'apostil_group1_id',
            'apostil_group2_id',
            'consul_id',
            'consulate_type_id',
        ];

        const sameScalars = scalarFields.every((field) => normalizedLeft[field] === normalizedRight[field]);
        if (!sameScalars) {
            return false;
        }

        if (normalizedLeft.selected_addons.length !== normalizedRight.selected_addons.length) {
            return false;
        }

        return normalizedLeft.selected_addons.every((item, index) => addonKey(item) === addonKey(normalizedRight.selected_addons[index]));
    };

    const getWrappers = () => Array.from(document.querySelectorAll('.wizard-wrapper'));

    const originalManagerInit = WizardManager.prototype.init;
    WizardManager.prototype.init = function () {
        const result = originalManagerInit.call(this);
        window.__documentWizardManager = this;

        return result;
    };

    const originalAddWizard = WizardManager.prototype.addWizard;
    WizardManager.prototype.addWizard = function (...args) {
        originalAddWizard.apply(this, args);

        const wrappers = getWrappers();

        return wrappers[wrappers.length - 1] || null;
    };

    const originalCollectData = WizardManager.prototype.collectData;
    WizardManager.prototype.collectData = function () {
        const data = originalCollectData.call(this);

        getWrappers().forEach((wrapper, index) => {
            if (!data[index]) {
                return;
            }

            const controller = wrapper._wizardController;
            const payload = controller ? controller.getProcessPayload() : null;

            data[index].selection_mode = payload?.selectionMode || '';
            data[index].package_template_id = '';
        });

        return data;
    };

    updateGlobalTotals = function () {
        let totalAmount = 0;
        let totalDiscount = 0;
        let finalAmount = 0;

        getWrappers().forEach((wrapper) => {
            const controller = wrapper._wizardController;
            if (!controller) {
                return;
            }

            const totals = controller.getTotals();
            totalAmount += Number(totals.totalAmount || 0);
            totalDiscount += Number(totals.discountAmount || 0);
            finalAmount += Number(totals.finalAmount || 0);
        });

        const totalService = document.getElementById('totalService');
        const totalDiscountNode = document.getElementById('totalDiscount');
        const finalPrice = document.getElementById('finalPrice');

        if (totalService) {
            totalService.textContent = totalAmount.toLocaleString();
        }

        if (totalDiscountNode) {
            totalDiscountNode.textContent = totalDiscount.toLocaleString();
        }

        if (finalPrice) {
            finalPrice.textContent = finalAmount.toLocaleString();
        }
    };

    WizardController.prototype.getBundleComparableConfig = function () {
        const payload = this.getProcessPayload();

        return normalizeComparableConfig({
            document_type_id: this.wrapper.querySelector('.doc-type')?.value || null,
            service_id: this.wrapper.querySelector('.service')?.value || null,
            process_mode: payload?.processMode || payload?.viewMode || 'service',
            selection_mode: payload?.selectionMode || null,
            direction_type_id: payload?.directionType || null,
            apostil_group1_id: payload?.apostil?.group1_id || null,
            apostil_group2_id: payload?.apostil?.group2_id || null,
            consul_id: payload?.consul?.consul_id || null,
            consulate_type_id: payload?.legalization?.id || null,
            selected_addons: this.getSelectedAddons(),
        });
    };

    WizardController.prototype.applyBundleSelectedAddons = function (selectedAddons, attempt = 0) {
        const targetKeys = new Set(normalizeAddons(selectedAddons).map(addonKey));

        return new Promise((resolve) => {
            let matched = 0;

            this.wrapper.querySelectorAll('.service-addon-checkbox').forEach((checkbox) => {
                const sourceType = checkbox.dataset.sourceType || checkbox.dataset.container || '';
                const id = Number(checkbox.dataset.id || 0);
                const key = `${sourceType}:${id}`;
                const shouldCheck = targetKeys.has(key);

                checkbox.checked = shouldCheck;
                checkbox.closest('.service-addon-item')?.classList.toggle('selected', shouldCheck);

                if (shouldCheck) {
                    matched += 1;
                }
            });

            ['document', 'direction', 'service'].forEach((type) => {
                try {
                    this.updateAddonTotal(type);
                } catch (error) {
                    // Section may not be visible yet.
                }
            });

            if (matched < targetKeys.size && attempt < 14) {
                setTimeout(() => {
                    this.applyBundleSelectedAddons(selectedAddons, attempt + 1).then(resolve);
                }, 180);

                return;
            }

            resolve();
        });
    };

    WizardController.prototype.applyBundleItemConfig = async function (item) {
        const click = (selector) => {
            const element = this.wrapper.querySelector(selector);
            if (element) {
                element.click();
            }
        };

        const setSelectValue = (selector, value) => {
            const element = this.wrapper.querySelector(selector);
            if (!element) {
                return;
            }

            element.value = value ? String(value) : '';
            element.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const setRadioValue = (selector, value) => {
            const element = value
                ? this.wrapper.querySelector(`${selector}[value="${value}"]`)
                : null;

            if (!element) {
                return;
            }

            element.checked = true;
            element.dispatchEvent(new Event('change', { bubbles: true }));
        };

        const normalizedItem = normalizeComparableConfig(item);

        setSelectValue('.doc-type', normalizedItem.document_type_id);
        await wait(120);

        const processMode = normalizedItem.process_mode || 'service';
        click(`.btn-process[data-mode="${processMode}"]`);
        await wait(120);

        if (processMode === 'apostil') {
            setRadioValue('.apostil-g1', normalizedItem.apostil_group1_id);
            await wait(80);
            setRadioValue('.apostil-g2', normalizedItem.apostil_group2_id);
            await wait(80);
            setSelectValue('.direction-type', normalizedItem.direction_type_id);
            await wait(120);
        }

        if (processMode === 'consul') {
            if (normalizedItem.selection_mode) {
                click(`.btn-legalization-mode[data-choice="${normalizedItem.selection_mode}"]`);
                await wait(80);
            }

            if (normalizedItem.consul_id) {
                setRadioValue('.consul-main', normalizedItem.consul_id);
                await wait(80);
            }

            if (normalizedItem.consulate_type_id) {
                setSelectValue('.legalization', normalizedItem.consulate_type_id);
                await wait(80);
            }
        }

        setSelectValue('.service', normalizedItem.service_id);
        await wait(220);
        await this.applyBundleSelectedAddons(normalizedItem.selected_addons);
    };

    const bundleManager = {
        activePackageId: null,
        exactMatch: false,
        isApplying: false,
        isSyncingDiscounts: false,

        get activePackage() {
            return packageMap.get(Number(this.activePackageId || 0)) || null;
        },

        clearWizardList() {
            const manager = window.__documentWizardManager;
            const container = document.getElementById('wizardContainer');

            if (container) {
                container.innerHTML = '';
            }

            if (manager) {
                manager.wizardIndex = 0;
            }

            updateGlobalTotals();
        },

        async applyPackage(packageId) {
            const pkg = packageMap.get(Number(packageId));
            const manager = window.__documentWizardManager;

            if (!pkg || !manager) {
                return;
            }

            this.isApplying = true;
            this.activePackageId = pkg.id;
            this.exactMatch = false;
            this.renderState(pkg, 'loading');
            this.clearWizardList();

            for (const item of (pkg.items || [])) {
                const wrapper = manager.addWizard();
                const controller = wrapper?._wizardController;

                if (!controller) {
                    continue;
                }

                await wait(40);
                await controller.applyBundleItemConfig(item);
            }

            this.isApplying = false;
            this.syncState();
        },

        unlockDiscounts(resetValues = true) {
            this.isSyncingDiscounts = true;

            getWrappers().forEach((wrapper) => {
                const discountInput = wrapper.querySelector('.discount');
                if (!discountInput) {
                    return;
                }

                if (resetValues || discountInput.dataset.bundleAuto === '1') {
                    discountInput.value = '0';
                }

                discountInput.readOnly = false;
                discountInput.classList.remove('bundle-discount-locked');
                discountInput.dataset.bundleAuto = '0';
                discountInput.dispatchEvent(new Event('input', { bubbles: true }));
            });

            this.isSyncingDiscounts = false;
        },

        applyDistributedDiscounts(pkg) {
            const wrappers = getWrappers();
            const baseAmounts = wrappers.map((wrapper) => Number(wrapper._wizardController?.getTotals().totalAmount || 0));
            const totalBase = baseAmounts.reduce((carry, item) => carry + item, 0);
            const targetSavings = Math.max(Math.round(Number(pkg?.savings_amount || 0)), 0);
            const distributedDiscounts = baseAmounts.map((baseAmount) => {
                if (baseAmount <= 0 || totalBase <= 0 || targetSavings <= 0) {
                    return 0;
                }

                return Math.min(Math.floor((targetSavings * baseAmount) / totalBase), baseAmount);
            });

            let remainingSavings = targetSavings - distributedDiscounts.reduce((carry, item) => carry + item, 0);

            for (let index = 0; index < distributedDiscounts.length && remainingSavings > 0; index += 1) {
                const capacity = Math.max(baseAmounts[index] - distributedDiscounts[index], 0);
                if (capacity <= 0) {
                    continue;
                }

                const addition = Math.min(capacity, remainingSavings);
                distributedDiscounts[index] += addition;
                remainingSavings -= addition;
            }

            this.isSyncingDiscounts = true;

            wrappers.forEach((wrapper, index) => {
                const discountInput = wrapper.querySelector('.discount');

                if (!discountInput) {
                    return;
                }

                discountInput.value = String(distributedDiscounts[index] || 0);
                discountInput.readOnly = true;
                discountInput.classList.add('bundle-discount-locked');
                discountInput.dataset.bundleAuto = '1';
                discountInput.dispatchEvent(new Event('input', { bubbles: true }));
            });

            this.isSyncingDiscounts = false;
        },

        clearActivePackage() {
            this.unlockDiscounts();
            this.activePackageId = null;
            this.exactMatch = false;
            this.renderState(null, 'idle');
        },

        syncState() {
            const pkg = this.activePackage;

            if (!pkg) {
                this.renderState(null, 'idle');
                return;
            }

            if (this.isApplying || this.isSyncingDiscounts) {
                return;
            }

            const wrappers = getWrappers();
            const exactMatch = wrappers.length === (pkg.items || []).length
                && wrappers.every((wrapper, index) => {
                    const controller = wrapper._wizardController;
                    const item = pkg.items?.[index];

                    if (!controller || !item) {
                        return false;
                    }

                    return sameConfig(controller.getBundleComparableConfig(), item);
                });

            if (exactMatch) {
                this.applyDistributedDiscounts(pkg);
                this.exactMatch = true;
                this.renderState(pkg, 'exact');
                updateGlobalTotals();
                return;
            }

            this.unlockDiscounts();
            this.exactMatch = false;
            this.renderState(pkg, 'mismatch');
            updateGlobalTotals();
        },

        renderState(pkg, state) {
            const title = picker.querySelector('.bundle-picker__title');
            const note = picker.querySelector('.bundle-picker__note');
            const clearButton = picker.querySelector('.bundle-picker__clear');
            const status = picker.querySelector('.bundle-picker__status');

            status?.classList.remove('is-exact', 'is-mismatch');

            picker.querySelectorAll('.bundle-card').forEach((card) => {
                const isSelected = pkg && Number(card.dataset.packageId) === Number(pkg.id);
                card.classList.toggle('is-selected', isSelected);
                card.classList.toggle('is-exact', isSelected && state === 'exact');
                card.classList.toggle('is-mismatch', isSelected && state === 'mismatch');
            });

            if (!pkg || state === 'idle') {
                if (title) {
                    title.textContent = 'Paket tanlanmagan';
                }

                if (note) {
                    note.textContent = "Paket tanlansa ichidagi barcha xizmatlar bo'yicha wizardlar tayyor holatda chiqadi.";
                }

                clearButton?.classList.add('d-none');
                return;
            }

            clearButton?.classList.remove('d-none');

            if (state === 'loading') {
                if (title) {
                    title.textContent = `${pkg.name} yuklanmoqda`;
                }

                if (note) {
                    note.textContent = `${pkg.item_count || 0} ta element formaga joylanmoqda.`;
                }

                return;
            }

            if (state === 'exact') {
                status?.classList.add('is-exact');

                if (title) {
                    title.textContent = `${pkg.name} paketi faol`;
                }

                if (note) {
                    note.textContent = `Fix narx ${money(pkg.promo_price)} bo'yicha hisoblanmoqda. Jami tejash: ${money(pkg.savings_amount)}.`;
                }

                return;
            }

            status?.classList.add('is-mismatch');

            if (title) {
                title.textContent = `${pkg.name} tanlangan`;
            }

            if (note) {
                note.textContent = "Tarkib o'zgargani uchun paket narxi bekor bo'ldi va forma oddiy tarif bo'yicha qayta hisoblandi.";
            }
        },
    };

    window.__bundlePackageManager = bundleManager;

    const bindUi = () => {
        picker.querySelectorAll('.bundle-card').forEach((card) => {
            card.addEventListener('click', () => {
                bundleManager.applyPackage(card.dataset.packageId);
            });
        });

        picker.querySelector('.bundle-picker__clear')?.addEventListener('click', () => {
            bundleManager.clearActivePackage();
        });

        document.getElementById('addWizard')?.addEventListener('click', () => {
            if (bundleManager.isApplying) {
                return;
            }

            setTimeout(() => bundleManager.syncState(), 0);
        });

        document.addEventListener('change', (event) => {
            if (bundleManager.isApplying || bundleManager.isSyncingDiscounts) {
                return;
            }

            if (!event.target.closest('.wizard-wrapper')) {
                return;
            }

            if (!event.target.matches('.doc-type, .service, .direction-type, .apostil-g1, .apostil-g2, .consul-main, .legalization, .service-addon-checkbox, .discount')) {
                return;
            }

            bundleManager.syncState();
        });

        document.addEventListener('click', (event) => {
            if (bundleManager.isApplying || bundleManager.isSyncingDiscounts) {
                return;
            }

            const action = event.target.closest('.btn-process, .btn-legalization-mode, .btn-legalization-reset, .btn-remove-wizard');
            if (!action || !action.closest('.wizard-wrapper')) {
                return;
            }

            setTimeout(() => bundleManager.syncState(), 0);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bindUi, { once: true });
    } else {
        bindUi();
    }
})();
</script>
