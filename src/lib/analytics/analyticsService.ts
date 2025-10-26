import { browser } from '$app/environment';
import { page } from '$app/stores';
import { supabase } from '$lib/supabase';
import { writable, derived, type Readable } from 'svelte/store';
import { v4 as uuidv4 } from 'uuid';
import { get } from 'svelte/store';

// Types for analytics data
export interface PageViewData {
	path: string;
	userId?: string;
	sessionId: string;
	isAuthenticated: boolean;
	browser?: string;
	deviceType?: string;
	referrer?: string;
	queryParams?: Record<string, string>;
}

export interface PageViewStats {
	path: string;
	views: number;
	uniqueUsers: number;
	uniqueSessions: number;
}

export interface DailyStats {
	date: string;
	totalPageviews: number;
	uniquePages: number;
	uniqueUsers: number;
	uniqueSessions: number;
	authenticatedViews: number;
	anonymousViews: number;
}

// Store to cache the session ID
const sessionStore = writable<string>(
	browser ? localStorage.getItem('analytics_session_id') || uuidv4() : ''
);

// Initialize session ID if in browser
if (browser) {
	const sessionId = get(sessionStore);
	localStorage.setItem('analytics_session_id', sessionId);
}

/**
 * Track a page view
 * @param pageData Optional additional page data
 * @returns Promise that resolves when tracking is complete
 */
export async function trackPageView(pageData?: Partial<PageViewData>): Promise<void> {
	if (!browser) return;

	try {
		const currentPage = get(page);
		const sessionId = get(sessionStore);
		const { pathname, search, href, host } = currentPage.url;

		// Parse query parameters
		const queryParams: Record<string, string> = {};
		const searchParams = new URLSearchParams(search);
		searchParams.forEach((value, key) => {
			queryParams[key] = value;
		});

		// Get browser and device info
		const userAgent = navigator.userAgent;
		const isMobile = /Mobi|Android/i.test(userAgent);
		const isTablet = /Tablet|iPad/i.test(userAgent);
		const deviceType = isTablet ? 'tablet' : isMobile ? 'mobile' : 'desktop';

		// Get browser info
		const browserInfo = (() => {
			if (userAgent.indexOf('Firefox') > -1) return 'Firefox';
			if (userAgent.indexOf('SamsungBrowser') > -1) return 'Samsung Browser';
			if (userAgent.indexOf('Opera') > -1 || userAgent.indexOf('OPR') > -1) return 'Opera';
			if (userAgent.indexOf('Trident') > -1) return 'Internet Explorer';
			if (userAgent.indexOf('Edge') > -1) return 'Edge';
			if (userAgent.indexOf('Chrome') > -1) return 'Chrome';
			if (userAgent.indexOf('Safari') > -1) return 'Safari';
			return 'Unknown';
		})();

		// Get authentication status from user data or parameter
		const {
			data: { session: authSession }
		} = await supabase.auth.getSession();
		const isAuthenticated = !!authSession;
		const userId = authSession?.user?.id;

		// Create page view data
		const pageViewData: PageViewData = {
			path: pathname,
			userId: userId,
			sessionId,
			isAuthenticated,
			browser: browserInfo,
			deviceType,
			referrer: document.referrer || undefined,
			queryParams: Object.keys(queryParams).length > 0 ? queryParams : undefined,
			...pageData
		};

		// Insert into Supabase
		const { error } = await supabase.from('page_analytics').insert({
			path: pageViewData.path,
			user_id: pageViewData.userId,
			session_id: pageViewData.sessionId,
			is_authenticated: pageViewData.isAuthenticated,
			browser: pageViewData.browser,
			device_type: pageViewData.deviceType,
			referrer: pageViewData.referrer,
			query_params: pageViewData.queryParams
		});

		if (error) {
			console.error('Error tracking page view:', error);
		}
	} catch (err) {
		console.error('Failed to track page view:', err);
	}
}

/**
 * Get daily page view statistics
 * @param days Number of days to fetch
 * @returns Promise with daily page view stats
 */
export async function getDailyPageViews(days: number = 7): Promise<PageViewStats[]> {
	try {
		// Calculate the date range
		const endDate = new Date();
		const startDate = new Date();
		startDate.setDate(startDate.getDate() - days);

		const { data, error } = await supabase
			.from('daily_page_views')
			.select('*')
			.gte('date', startDate.toISOString().split('T')[0])
			.lte('date', endDate.toISOString().split('T')[0]);

		if (error) {
			console.error('Error fetching daily page views:', error);
			return [];
		}

		return data.map((item) => ({
			path: item.path,
			views: item.views,
			uniqueUsers: item.unique_users,
			uniqueSessions: item.unique_sessions
		}));
	} catch (err) {
		console.error('Failed to get daily page views:', err);
		return [];
	}
}

/**
 * Get monthly page view statistics
 * @param months Number of months to fetch
 * @returns Promise with monthly page view stats
 */
export async function getMonthlyPageViews(months: number = 3): Promise<PageViewStats[]> {
	try {
		// Calculate the date range
		const endDate = new Date();
		const startDate = new Date();
		startDate.setMonth(startDate.getMonth() - months);

		const { data, error } = await supabase
			.from('monthly_page_views')
			.select('*')
			.gte('month', startDate.toISOString().split('T')[0])
			.lte('month', endDate.toISOString().split('T')[0]);

		if (error) {
			console.error('Error fetching monthly page views:', error);
			return [];
		}

		return data.map((item) => ({
			path: item.path,
			views: item.views,
			uniqueUsers: item.unique_users,
			uniqueSessions: item.unique_sessions
		}));
	} catch (err) {
		console.error('Failed to get monthly page views:', err);
		return [];
	}
}

/**
 * Get aggregated dashboard statistics
 * @param days Number of days to fetch
 * @returns Promise with dashboard stats
 */
export async function getDashboardStats(days: number = 30): Promise<DailyStats[]> {
	try {
		// Calculate the date range
		const endDate = new Date();
		const startDate = new Date();
		startDate.setDate(startDate.getDate() - days);

		const { data, error } = await supabase
			.from('analytics_dashboard_data')
			.select('*')
			.gte('date', startDate.toISOString().split('T')[0])
			.lte('date', endDate.toISOString().split('T')[0])
			.order('date', { ascending: false });

		if (error) {
			console.error('Error fetching dashboard stats:', error);
			return [];
		}

		return data.map((item) => ({
			date: item.date,
			totalPageviews: item.total_pageviews,
			uniquePages: item.unique_pages,
			uniqueUsers: item.unique_users,
			uniqueSessions: item.unique_sessions,
			authenticatedViews: item.authenticated_views,
			anonymousViews: item.anonymous_views
		}));
	} catch (err) {
		console.error('Failed to get dashboard stats:', err);
		return [];
	}
}

/**
 * Get real-time online user count (active in last 5 minutes)
 * @returns Promise with count of active users
 */
export async function getActiveUsers(): Promise<number> {
	try {
		// Get timestamp for 5 minutes ago
		const fiveMinutesAgo = new Date();
		fiveMinutesAgo.setMinutes(fiveMinutesAgo.getMinutes() - 5);

		const { count, error } = await supabase
			.from('page_analytics')
			.select('session_id', { count: 'exact', head: true })
			.gte('created_at', fiveMinutesAgo.toISOString())
			.order('created_at', { ascending: false });

		if (error) {
			console.error('Error fetching active users:', error);
			return 0;
		}

		return count || 0;
	} catch (err) {
		console.error('Failed to get active users:', err);
		return 0;
	}
}

/**
 * Get most popular pages
 * @param limit Number of pages to fetch
 * @param days Number of days to include
 * @returns Promise with popular pages stats
 */
export async function getPopularPages(
	limit: number = 10,
	days: number = 30
): Promise<PageViewStats[]> {
	try {
		// Calculate the date range
		const endDate = new Date();
		const startDate = new Date();
		startDate.setDate(startDate.getDate() - days);

		const { data, error } = await supabase
			.from('daily_page_views')
			.select('*')
			.gte('date', startDate.toISOString().split('T')[0])
			.lte('date', endDate.toISOString().split('T')[0])
			.order('views', { ascending: false })
			.limit(limit);

		if (error) {
			console.error('Error fetching popular pages:', error);
			return [];
		}

		// Aggregate by path
		const pageMap = new Map<string, PageViewStats>();

		data.forEach((item) => {
			const path = item.path;
			if (!pageMap.has(path)) {
				pageMap.set(path, {
					path,
					views: 0,
					uniqueUsers: 0,
					uniqueSessions: 0
				});
			}

			const pageStats = pageMap.get(path)!;
			pageStats.views += item.views;
			pageStats.uniqueUsers += item.unique_users;
			pageStats.uniqueSessions += item.unique_sessions;
		});

		// Convert map to array and sort by views
		return Array.from(pageMap.values())
			.sort((a, b) => b.views - a.views)
			.slice(0, limit);
	} catch (err) {
		console.error('Failed to get popular pages:', err);
		return [];
	}
}
