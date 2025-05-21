import { PUBLIC_SUPABASE_URL, PUBLIC_SUPABASE_ANON_KEY } from '$env/static/public';
import { browser } from '$app/environment';

// Define configuration interface
interface Config {
    supabase: {
        url: string;
        anonKey: string;
    };
    environment: string;
    isDevelopment: boolean;
    isProduction: boolean;
    isTest: boolean;
    debug: boolean;
    appUrl: string;
    logging: {
        level: 'debug' | 'info' | 'warn' | 'error' | 'none';
        sanitize: boolean;
    };
    security: {
        csrfProtection: boolean;
        strictHeaders: boolean;
        rateLimiting: boolean;
        cspNonce: boolean;
    };
}

// Function to get environment with fallbacks
function getEnvironment(): string {
    if (browser) {
        return import.meta.env?.MODE || 'development';
    }
    return process.env.NODE_ENV || 'development';
}

// Default configuration
const defaultConfig: Config = {
    supabase: {
        url: PUBLIC_SUPABASE_URL,
        anonKey: PUBLIC_SUPABASE_ANON_KEY
    },
    environment: getEnvironment(),
    isDevelopment: false,
    isProduction: false,
    isTest: false,
    debug: false,
    appUrl: 'https://cv-app.vercel.app',
    logging: {
        level: 'info',
        sanitize: true
    },
    security: {
        csrfProtection: true,
        strictHeaders: true,
        rateLimiting: true,
        cspNonce: false
    }
};

// Set environment flags
defaultConfig.isDevelopment = defaultConfig.environment === 'development';
defaultConfig.isProduction = defaultConfig.environment === 'production';
defaultConfig.isTest = defaultConfig.environment === 'test';

// Environment-specific configuration overrides
const envConfigs: Record<string, Partial<Config>> = {
    development: {
        debug: true,
        appUrl: 'http://localhost:5173',
        logging: {
            level: 'debug',
            sanitize: true
        },
        security: {
            csrfProtection: true,
            strictHeaders: true,
            rateLimiting: false,
            cspNonce: false
        }
    },
    test: {
        debug: false,
        logging: {
            level: 'error',
            sanitize: true
        }
    },
    production: {
        debug: false,
        logging: {
            level: 'warn',
            sanitize: true
        },
        security: {
            csrfProtection: true,
            strictHeaders: true,
            rateLimiting: true,
            cspNonce: false
        }
    }
};

// Create the final config by merging default with environment-specific
const config: Config = {
    ...defaultConfig,
    ...(envConfigs[defaultConfig.environment] || {})
};

// Validate configuration
function validateConfig(cfg: Config): void {
    const requiredFields = [
        { path: 'supabase.url', value: cfg.supabase.url },
        { path: 'supabase.anonKey', value: cfg.supabase.anonKey }
    ];

    const missingFields = requiredFields.filter((field) => !field.value).map((field) => field.path);

    if (missingFields.length > 0) {
        throw new Error(
            `Missing required configuration: ${missingFields.join(', ')}. ` +
            'Please check your environment variables.'
        );
    }

    // Log configuration details (but not sensitive values)
    if (cfg.debug && browser) {
        console.log(`App Configuration (${cfg.environment}):`);
        console.log(`  • Debug mode: ${cfg.debug}`);
        console.log(`  • Logging level: ${cfg.logging.level}`);
        console.log(`  • Security features enabled:`);
        console.log(`    - CSRF Protection: ${cfg.security.csrfProtection}`);
        console.log(`    - Strict Headers: ${cfg.security.strictHeaders}`);
        console.log(`    - Rate Limiting: ${cfg.security.rateLimiting}`);
        console.log(`    - CSP Nonce: ${cfg.security.cspNonce}`);
    }
}

// Safe logging function that avoids logging sensitive information
export function safeLog(
    level: 'debug' | 'info' | 'warn' | 'error',
    message: string,
    data?: any
): void {
    if (!browser) return;

    const logLevels = { debug: 0, info: 1, warn: 2, error: 3, none: 4 };
    const configLevel = config.logging.level;

    if (logLevels[level] < logLevels[configLevel]) return;

    // Don't log in production unless it's an error or configured
    if (config.isProduction && level === 'debug') return;

    // Sanitize sensitive data if configured
    let sanitizedData;
    if (data && config.logging.sanitize) {
        sanitizedData = typeof data === 'object' ? { ...data } : data;

        // Fields to sanitize (expand as needed)
        const sensitiveFields = ['password', 'token', 'key', 'secret', 'credentials', 'auth'];

        if (typeof sanitizedData === 'object') {
            for (const field of sensitiveFields) {
                if (field in sanitizedData) {
                    sanitizedData[field] = '[REDACTED]';
                }
            }
        }
    }

    switch (level) {
        case 'debug':
            console.debug(message, sanitizedData || '');
            break;
        case 'info':
            console.info(message, sanitizedData || '');
            break;
        case 'warn':
            console.warn(message, sanitizedData || '');
            break;
        case 'error':
            console.error(message, sanitizedData || '');
            break;
    }
}

// Validate configuration on initialization
validateConfig(config);

export default config;
