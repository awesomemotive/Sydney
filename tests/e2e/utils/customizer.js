import { SITE_CONFIG } from './constants.js';

export function setCustomizerSetting(settingKey, settingValue) {
	fetch(SITE_CONFIG.CUSTOMIZER_API_URL, {
	  method: 'POST',
	  headers: {
		'Content-Type': 'application/json',
	  },
	  body: JSON.stringify({
		setting_key: settingKey,
		setting_value: settingValue
	  })
	})
	.then(response => {
	  if (!response.ok) {
		throw new Error('Failed to set customizer setting value.');
	  }
	  return response.json();
	})
	.then(data => {
	})
	.catch(error => {
	  console.error(error);
	});
}