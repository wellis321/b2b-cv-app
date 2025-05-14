import config, { safeLog } from '$lib/config';

// In-memory store for rate limiting (will reset on server restart)
// In production, you'd want to use Redis or a similar persistent store
const requestLogs: Record<string, { count: number; resetTime: number }> = {};

// Cleanup expired entries periodically (every 10 minutes)
if (typeof setInterval !== 'undefined') {
	setInterval(
		() => {
			const now = Date.now();
			for (const key in requestLogs) {
				if (requestLogs[key].resetTime < now) {
					delete requestLogs[key];
				}
			}
		},
		10 * 60 * 1000
	);
}

export interface RateLimitOptions {
	// Maximum number of requests allowed in the time window
	max: number;
	// Time window in seconds
	windowMs: number;
	// Message to return when rate limit is exceeded
	message?: string;
	// Function to determine the key to use for rate limiting
	keyGenerator?: (request: Request, locals: any) => string;
}

/**
 * Rate limiting middleware for SvelteKit
 */
export function rateLimit(options: RateLimitOptions) {
	const {
		max = 100,
		windowMs = 60 * 1000, // 1 minute
		message = 'Too many requests, please try again later',
		keyGenerator = (request, locals) => {
			// Default key is IP address and path, or just path if no IP available
			const ip = request.headers.get('x-forwarded-for') || 'unknown';
			return `${ip}:${request.url}`;
		}
	} = options;

	return async (request: Request, locals: any) => {
		// Skip rate limiting if not enabled in config
		if (!config.security.rateLimiting) {
			return null;
		}

		try {
			const key = keyGenerator(request, locals);
			const now = Date.now();

			// Initialize or get the record for this key
			if (!requestLogs[key] || requestLogs[key].resetTime < now) {
				requestLogs[key] = {
					count: 0,
					resetTime: now + windowMs
				};
			}

			// Increment the count
			requestLogs[key].count++;

			// Check if rate limit is exceeded
			if (requestLogs[key].count > max) {
				// Reset after window expires
				const timeUntilReset = Math.max(0, requestLogs[key].resetTime - now);
				const resetInSeconds = Math.ceil(timeUntilReset / 1000);

				safeLog('warn', 'Rate limit exceeded', {
					key,
					count: requestLogs[key].count,
					max,
					resetIn: resetInSeconds
				});

				// Return a response with status 429 (Too Many Requests)
				return new Response(JSON.stringify({ error: message }), {
					status: 429,
					headers: {
						'Content-Type': 'application/json',
						'Retry-After': resetInSeconds.toString()
					}
				});
			}

			return null; // Proceed with the request
		} catch (error) {
			safeLog('error', 'Error in rate limiter', { error });
			return null; // Proceed with the request on error
		}
	};
}

/**
 * Helper function to apply rate limiting to authentication routes
 */
export function applyAuthRateLimit(request: Request, locals: any) {
	return rateLimit({
		max: 10, // 10 attempts
		windowMs: 15 * 60 * 1000, // 15 minutes
		message: 'Too many authentication attempts, please try again later',
		keyGenerator: (req) => {
			// Use IP address as the key for auth rate limiting
			const ip = req.headers.get('x-forwarded-for') || 'unknown';
			return `auth:${ip}`;
		}
	})(request, locals);
}
