import { SITE_CONFIG } from './constants.js';

export async function activatePlugin( slug, page ) {

	await page.goto(`${SITE_CONFIG.ADMIN_URL}/plugins.php`);
	
	const disableLink = await page.$(
		`tr[data-slug="${ slug }"] .deactivate a`
	);
	if ( disableLink ) {
		return;
	}
	await page.click( `tr[data-slug="${ slug }"] .activate a` );
	await page.waitForSelector( `tr[data-slug="${ slug }"] .deactivate a` );
}

export async function deactivatePlugin( slug, page ) {

	await page.goto(`${SITE_CONFIG.ADMIN_URL}/plugins.php`);

	const deleteLink = await page.$( `tr[data-slug="${ slug }"] .delete a` );
	if ( deleteLink ) {
		return;
	}
	await page.click( `tr[data-slug="${ slug }"] .deactivate a` );
	
	if ( slug === 'elementor' ) {
		await page.click( `.dialog-lightbox-skip` );
		await page.waitForSelector( `tr[data-slug="${ slug }"] .delete a` );
	} else {
		await page.waitForSelector( `tr[data-slug="${ slug }"] .delete a` );
	}
}