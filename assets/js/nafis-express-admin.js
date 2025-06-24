const popup = (title = "عنوان پاپ اپ", content = "در حال بارگذاری...") => {
  let element = {
    overlay: null,
    content: null,
  };
  const overlay = document.getElementById("nafis-popup-overlay");
  const staticContent = `<div
            id="nafis-track-popup"
            style="background:#fff; max-width:700px; margin:50px auto; padding:20px; border-radius:8px; position:relative;"
          >
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <h2 style="margin:0;">${title}</h2>
              <button
                onclick="document.getElementById('nafis-popup-overlay').style.display='none'"
                class="button"
              >
                بستن
              </button>
            </div>
            <div id="nafis-popup-content" style="margin-top:1rem;">
              ${content}
            </div>
          </div>`;

  if (!!overlay) {
    overlay.innerHTML = staticContent;
    overlay.style.display = "block";
    element.overlay = overlay;
    element.content = document.getElementById("nafis-popup-content");
  } else {
    const nafis_track_popup_overlay = document.createElement("div");
    nafis_track_popup_overlay.id = "nafis-popup-overlay";
    nafis_track_popup_overlay.style =
      "display:block; position:fixed; z-index:9999; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5);";
    nafis_track_popup_overlay.innerHTML = staticContent;
    document.body.appendChild(nafis_track_popup_overlay);
    element.overlay = nafis_track_popup_overlay;
    element.content = document.getElementById("nafis-popup-content");
  }

  return element;
};

document.addEventListener("DOMContentLoaded", () => {
  /**
   * Order EXited
   */
  document.querySelectorAll(".track-barcode-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const barcode = button.getAttribute("data-barcode");
      const { content } = popup("رهگیری بارکد");

      fetch(
        `${nafisExpressData.ajaxurl}?action=nafis_track_barcode&barcode=${barcode}&nonce=${nafisExpressData.nonce}`
      )
        .then((res) => res.json())
        .then((data) => {
          if (!data || !data.logs) {
            content.innerHTML = "<p>اطلاعاتی یافت نشد.</p>";
            return;
          }

          const header = `
                        <p><strong>شرکت:</strong> ${data.companyTitle}</p>
                        <p><strong>گیرنده:</strong> ${
                          data.receiverName
                        } | موبایل: ${data.receiverMobile}</p>
                        ${
                          !!data.agentName
                            ? `<p><strong>مامور توزیع:</strong> ${data.agentName}</p>`
                            : ""
                        }
                        <p><strong>مبداً:</strong> ${
                          data.startCity
                        } → <strong>مقصد:</strong> ${data.finalCity}</p>
                        <p><strong>شماره سفارش:</strong> ${
                          data.orderID
                        } | وزن: ${data.weight} گرم</p>
                        <hr>
                    `;

          const logTable = `
                        <table style="width:100%; border-collapse:collapse; text-align:right;">
                            <thead>
                                <tr>
                                    <th style="border-bottom:1px solid #ccc; padding:8px;">تاریخ</th>
                                    <th style="border-bottom:1px solid #ccc; padding:8px;">وضعیت</th>
                                    <th style="border-bottom:1px solid #ccc; padding:8px;">شعبه</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.logs
                                  .map(
                                    (log) => `
                                    <tr>
                                        <td style="border-bottom:1px solid #eee; padding:6px;">${
                                          log.statusDateTimeString
                                        }</td>
                                        <td style="border-bottom:1px solid #eee; padding:6px;">${
                                          log.statusTitle
                                        }</td>
                                        <td style="border-bottom:1px solid #eee; padding:6px;">${
                                          log.branchTitle || "-"
                                        }</td>
                                    </tr>
                                `
                                  )
                                  .join("")}
                            </tbody>
                        </table>
                    `;

          content.innerHTML = header + logTable;
        })
        .catch(() => {
          content.innerHTML = "<p>خطا در ارتباط با سرور.</p>";
        });
    });
  });

  /**
   * Issue Barcode
   */
  document.querySelectorAll(".nafis-barcode-btn").forEach((button) => {
    button.addEventListener("click", () => {
      const { content } = popup("جزئیات صدور بارکد");
      const raw = button.dataset.order;
      if (!raw) return;

      let data;
      try {
        data = JSON.parse(raw);
      } catch (err) {
        console.error("Invalid JSON in data-order:", err);
        return;
      }

      const infoTable = `
        <table style="width:100%; border-collapse:collapse; text-align:right; margin-bottom: 1rem;">
          <tbody>
              <tr>
                <td style="border-bottom:1px solid #eee; padding:6px;">نام</td>
                <td style="border-bottom:1px solid #eee; padding:6px;">${data.receiverFirstName + " " + data.receiverLastName}</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #eee; padding:6px;">موبایل</td>
                <td style="border-bottom:1px solid #eee; padding:6px;">${data.receiverMobile}</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #eee; padding:6px;">کدپستی</td>
                <td style="border-bottom:1px solid #eee; padding:6px;">${data.postcode ?? "-----------"}</td>
              </tr>
              <tr>
                <td style="border-bottom:1px solid #eee; padding:6px;">آدرس</td>
                <td style="border-bottom:1px solid #eee; padding:6px;">${data.address}</td>
              </tr>

            </tbody>
        </table>
      `;

      const productList = `
        <table style="width:100%; border-collapse:collapse; text-align:right;">
          <thead>
            <tr><th style="border-bottom:1px solid #ccc; padding:8px;">محصول</th><th style="border-bottom:1px solid #ccc; padding:8px;">تعداد</th></tr>
          </thead>
          <tbody>
            ${data.products
              .map(
                (p) => `
              <tr>
                <td style="border-bottom:1px solid #eee; padding:6px;">${p.name}</td>
                <td style="border-bottom:1px solid #eee; padding:6px;">${p.qty}</td>
              </tr>
            `
              )
              .join("")}
          </tbody>
        </table>
      `;

      const footer = `
        <div style="text-align:right; margin-top:15px;">
          <button class="button button-primary" id="nafis-barcode-confirm-btn">تایید صدور</button>
          <button class="button button-secondary" onclick="document.getElementById('nafis-popup-overlay').style.display='none'">لغو</button>
        </div>
      `;

      content.innerHTML = `<h3>اطلاعات سفارش</h3>${infoTable}<h3>محصولات</h4>${productList}${footer}`;
    });
  });
});
