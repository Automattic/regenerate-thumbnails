import axios from 'axios';

export const WPRESTAPIAsync = axios.create({
	baseURL: regenerateThumbnails.wpApiSettings.root,
	headers: {
		"X-WP-Nonce": regenerateThumbnails.wpApiSettings.nonce,
	}
});
