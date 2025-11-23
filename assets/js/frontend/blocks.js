(() => {
  "use strict";
  const e = window.React,
    t = window.wp.i18n,
    n = window.wc.wcBlocksRegistry,
    a = window.wp.htmlEntities,
    c = (0, window.wc.wcSettings.getSetting)("ocpay_data", {}),
    i = (0, t.__)("OCPay", "ocpay-gateway"),
    o = (0, a.decodeEntities)(c.title) || i,
    l = () => (0, a.decodeEntities)(c.description || ""),
    s = {
      name: "ocpay",
      label: (0, e.createElement)((t) => {
        const { PaymentMethodLabel: n } = t.components;
        return (0, e.createElement)(n, {
          text: o
        });
      }, null),
      content: (0, e.createElement)(l, null),
      edit: (0, e.createElement)(l, null),
      canMakePayment: () => !0,
      ariaLabel: o,
      supports: {
        features: c.supports,
      },
    };
  (0, n.registerPaymentMethod)(s);
})();
