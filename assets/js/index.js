!function () {
    "use strict";
    const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
    const { getSetting } = window.wc.wcSettings;
    const { createElement } = window.wp.element;
    const { decodeEntities } = window.wp.htmlEntities;
    const { __ } = window.wp.i18n;

    const getSBHData = () => {
        const data = getSetting("ttckvsbh_data", null);
        if (!data) throw new Error("SBH missing data");
        return data;
    };

    const sbhData = getSBHData();
    const getDescription = () => decodeEntities(sbhData.description || "");
    const getTitle = () => decodeEntities(sbhData.title || "");

    registerPaymentMethod({
        name: "ttckvsbh",
        label: createElement('div', {},
            createElement('div', null, getTitle()),
            createElement("img", { src: sbhData.logo_url, alt: sbhData.title })
        ),
        ariaLabel: __("SBH payment method", "woocommerce-gateway-sbh"),
        canMakePayment: () => true,
        content: createElement('div', {},
            createElement('div', null, getDescription()),
            createElement('div', { className: 'ttckvsbh-row' }, null),
        ),
        edit: createElement('div', {},
            createElement('div', null, getDescription()),
            createElement('div', { className: 'ttckvsbh-row' }, null),
        ),
        supports: { features: sbhData.supports || [] }
    })
}();