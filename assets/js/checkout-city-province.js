document.addEventListener('DOMContentLoaded', function () {
  const mappings = [
    { province: '#billing_state', city: '#billing_city' },
    { province: '#shipping_state', city: '#shipping_city' },
  ];

  mappings.forEach(({ province, city }) => {
    const provinceSelect = document.querySelector(province);
    const citySelect = document.querySelector(city);

    if (!provinceSelect || !citySelect || !window.nafisProvinces) return;

    // پاک‌سازی اولیه و افزودن گزینه پیش‌فرض
    provinceSelect.innerHTML = '<option value="">انتخاب استان</option>';
    window.nafisProvinces.forEach(province => {
      const opt = document.createElement('option');
      opt.value = 'nafis_' + province.id;
      opt.textContent = province.name;
      provinceSelect.appendChild(opt);
    });

    // 👇 تأخیر برای dispatch اگر استان از قبل ست شده
    setTimeout(() => {
      const savedProvince = provinceSelect.getAttribute('data-input-raw') || provinceSelect.value;
      if (savedProvince) {
        provinceSelect.value = savedProvince;
        provinceSelect.dispatchEvent(new Event('change'));
      }
    }, 100); // 100 میلی‌ثانیه برای اطمینان

    provinceSelect.addEventListener('change', function () {
      const selectedId = this.value;
      citySelect.disabled = true;
      citySelect.innerHTML = '<option>در حال بارگذاری...</option>';
    
      if (!selectedId) {
        citySelect.innerHTML = '<option>ابتدا استان را انتخاب کنید</option>';
        return;
      }
    
      const url = `${nafisExpressData.ajaxurl}?action=nopriv_nafis_get_cities_by_province&province_id=${selectedId}&nonce=${nafisExpressData.nonce}`;
    
      fetch(url)
        .then((res) => res.json())
        .then((res) => {
          citySelect.innerHTML = '<option value="">انتخاب شهر</option>';
          if (res.success && Array.isArray(res.data)) {
            res.data.forEach(city => {
              const opt = document.createElement('option');
              opt.value = city.name;
              opt.textContent = city.name;
              citySelect.appendChild(opt);
            });
    
            const savedCity = citySelect.getAttribute('data-input-raw') || citySelect.value;
            if (savedCity) {
              citySelect.value = savedCity;
            }
          } else {
            citySelect.innerHTML = '<option>هیچ شهری یافت نشد</option>';
          }
          citySelect.disabled = false;
        })
        .catch(() => {
          citySelect.innerHTML = '<option>خطا در دریافت شهرها</option>';
          citySelect.disabled = false;
        });
    });
    
  });
});
