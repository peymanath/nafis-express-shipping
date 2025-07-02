document.addEventListener('DOMContentLoaded', function () {
  const normalizeFa = (str) =>
    (str || '').replace(/ي/g, 'ی').replace(/ك/g, 'ک').replace(/\u200c/g, '').trim();

  const mappings = [
    { province: '#billing_state', city: '#billing_city' },
    { province: '#shipping_state', city: '#shipping_city' },
  ];

  mappings.forEach(({ province, city }) => {
    const provinceSelect = document.querySelector(province);
    const citySelect = document.querySelector(city);

    if (!provinceSelect || !citySelect || !window.nafisProvinceOptions || !window.nafisCityList) return;

    const savedProvince = provinceSelect.value;
    const savedCityRaw = citySelect.value;
    const savedCity = normalizeFa(savedCityRaw);

    const fillCities = (provinceValue, preselectCity) => {
      const cleanId = provinceValue.replace(/^nafis_/, '');
      const cities = window.nafisCityList.filter(c => String(c.provinceID) === cleanId);

      citySelect.disabled = true;
      citySelect.innerHTML = '<option value="">انتخاب شهر</option>';

      cities.forEach(c => {
        const cityName = normalizeFa(c.name);
        const option = document.createElement('option');
        option.value = cityName;
        option.textContent = cityName;
        citySelect.appendChild(option);
      });

      if (preselectCity) {
        const matched = Array.from(citySelect.options).find(opt => normalizeFa(opt.value) === preselectCity);
        if (matched) matched.selected = true;
      }

      citySelect.disabled = false;
    };

    // Pre-fill if province is selected and city not properly loaded
    if (savedProvince && (!savedCityRaw || citySelect.options.length <= 1)) {
      fillCities(savedProvince, savedCity);
    }

    provinceSelect.addEventListener('change', function () {
      fillCities(this.value, '');
    });
  });
});
