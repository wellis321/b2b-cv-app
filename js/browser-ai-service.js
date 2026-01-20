/**
 * Browser AI Service
 * Handles AI execution in the browser using WebLLM and TensorFlow.js
 */

const BrowserAIService = {
    webllmEngine: null,
    webllmModule: null, // Store the imported WebLLM module
    tensorflowModel: null,
    currentModel: null,
    currentModelType: null,
    
    /**
     * Initialize browser AI with selected model
     * @param {string} modelType 'webllm' or 'tensorflow'
     * @param {string} modelName Model name (e.g., 'llama3.2', 'mistral')
     * @param {Function} progressCallback Callback for progress updates
     * @returns {Promise<void>}
     */
    async initBrowserAI(modelType, modelName, progressCallback = null) {
        try {
            this.currentModelType = modelType;
            this.currentModel = modelName;
            
            if (modelType === 'webllm') {
                return await this.initWebLLM(modelName, progressCallback);
            } else if (modelType === 'tensorflow') {
                return await this.initTensorFlow(modelName, progressCallback);
            } else {
                throw new Error(`Unknown model type: ${modelType}`);
            }
        } catch (error) {
            console.error('Error initializing browser AI:', error);
            throw error;
        }
    },
    
    /**
     * Map user-friendly model names to WebLLM model IDs
     * @param {string} modelName User-friendly model name (e.g., 'llama3.2', 'llama3')
     * @returns {string} WebLLM model ID
     */
    mapModelNameToWebLLMID(modelName) {
        const modelMap = {
            'llama3.2': 'Llama-3.1-8B-Instruct-q4f32_1-MLC',
            'llama3': 'Llama-3.1-8B-Instruct-q4f32_1-MLC',
            'llama3.1': 'Llama-3.1-8B-Instruct-q4f32_1-MLC',
            'llama-3.2': 'Llama-3.1-8B-Instruct-q4f32_1-MLC',
            'llama-3.1': 'Llama-3.1-8B-Instruct-q4f32_1-MLC',
            'llama-3': 'Llama-3.1-8B-Instruct-q4f32_1-MLC',
            'mistral': 'Mistral-7B-Instruct-v0.3-q4f32_1-MLC',
            'phi3': 'Phi-3-mini-4k-instruct-q4f32_1-MLC',
            'gemma': 'Gemma-2-2b-it-q4f32_1-MLC',
        };
        
        // If it's already a WebLLM model ID (contains dashes and MLC), use as-is
        if (modelName.includes('-') && modelName.includes('MLC')) {
            return modelName;
        }
        
        // Map to WebLLM model ID, default to Llama 3.1 if not found
        return modelMap[modelName.toLowerCase()] || 'Llama-3.1-8B-Instruct-q4f32_1-MLC';
    },
    
    /**
     * Initialize WebLLM model
     * @param {string} modelName Model name (user-friendly or WebLLM ID)
     * @param {Function} progressCallback Progress callback
     * @returns {Promise<void>}
     */
    async initWebLLM(modelName, progressCallback = null) {
        try {
            // Check if WebLLM module is loaded
            if (!this.webllmModule) {
                // Load WebLLM dynamically using import()
                await this.loadWebLLMLibrary();
            }
            
            // Get webllm from the imported module
            let webllm = this.webllmModule;
            if (!webllm) {
                throw new Error('WebLLM module loaded but exports not available');
            }
            
            // Handle default export if present
            if (webllm.default && !webllm.CreateMLCEngine) {
                webllm = webllm.default;
                this.webllmModule = webllm;
            }
            
            // Check if the module has the expected exports
            if (!webllm.CreateMLCEngine) {
                console.warn('WebLLM module structure:', Object.keys(webllm));
                throw new Error('WebLLM module loaded but CreateMLCEngine not found. Available exports: ' + Object.keys(webllm).join(', '));
            }
            
            // Map user-friendly model name to WebLLM model ID
            const webllmModelId = this.mapModelNameToWebLLMID(modelName);
            
            // Check for cached model (use original name for cache lookup)
            const cachedModel = await ModelCacheManager.getModel(modelName, 'webllm');
            
            if (progressCallback) {
                progressCallback({ stage: 'checking_cache', message: 'Checking for cached model...' });
            }
            
            // WebLLM uses CreateMLCEngine with model name and config
            const engineConfig = {
                initProgressCallback: (progress) => {
                    if (progressCallback) {
                        progressCallback({
                            stage: 'downloading',
                            progress: progress.progress || 0,
                            message: progress.text || `Downloading model: ${Math.round((progress.progress || 0) * 100)}%`
                        });
                    }
                }
            };
            
            // Use CreateMLCEngine (CreateWebWorkerMLCEngine has different API signature)
            const CreateMLCEngine = webllm.CreateMLCEngine || webllm.default?.CreateMLCEngine || (webllm.default && webllm.default.CreateMLCEngine) || webllm.CreateWebLLMEngine;
            
            if (!CreateMLCEngine) {
                throw new Error('CreateMLCEngine not found in WebLLM module. Available exports: ' + Object.keys(webllm).join(', '));
            }
            
            // Create engine with WebLLM model ID and config
            this.webllmEngine = await CreateMLCEngine(webllmModelId, engineConfig);
            
            // Cache the model for future use (if getModelCache method exists)
            if (!cachedModel && this.webllmEngine && typeof this.webllmEngine.getModelCache === 'function') {
                if (progressCallback) {
                    progressCallback({ stage: 'caching', message: 'Caching model for future use...' });
                }
                try {
                    await ModelCacheManager.saveModel({
                        modelName: modelName,
                        modelType: 'webllm',
                        data: this.webllmEngine.getModelCache(),
                        size: this.estimateModelSize(modelName),
                    });
                } catch (cacheError) {
                    // Cache saving is optional, log but don't fail
                    console.warn('Failed to cache model:', cacheError);
                }
            }
            
            return Promise.resolve();
        } catch (error) {
            console.error('Error initializing WebLLM:', error);
            throw new Error(`Failed to initialize WebLLM model ${modelName}: ${error.message}`);
        }
    },
    
    /**
     * Initialize TensorFlow.js model
     * @param {string} modelName Model name
     * @param {Function} progressCallback Progress callback
     * @returns {Promise<void>}
     */
    async initTensorFlow(modelName, progressCallback = null) {
        try {
            // Load TensorFlow.js if not already loaded
            if (typeof tf === 'undefined') {
                await this.loadTensorFlowLibrary();
            }
            
            // Check for cached model
            const cachedModel = await ModelCacheManager.getModel(modelName, 'tensorflow');
            
            if (progressCallback) {
                progressCallback({ stage: 'checking_cache', message: 'Checking for cached model...' });
            }
            
            // Load model (simplified - would need actual model URL)
            // This is a placeholder - actual implementation would load from URL or cache
            if (cachedModel && cachedModel.data) {
                this.tensorflowModel = await tf.loadLayersModel(cachedModel.data);
            } else {
                // Load from URL (would need actual model URL)
                const modelUrl = this.getTensorFlowModelUrl(modelName);
                if (progressCallback) {
                    progressCallback({ stage: 'downloading', message: 'Downloading TensorFlow.js model...' });
                }
                this.tensorflowModel = await tf.loadLayersModel(modelUrl);
                
                // Cache the model
                await ModelCacheManager.saveModel({
                    modelName: modelName,
                    modelType: 'tensorflow',
                    data: modelUrl, // Store URL or model data
                    size: this.estimateModelSize(modelName, 'tensorflow'),
                });
            }
            
            return Promise.resolve();
        } catch (error) {
            console.error('Error initializing TensorFlow.js:', error);
            throw new Error(`Failed to initialize TensorFlow.js model ${modelName}: ${error.message}`);
        }
    },
    
    /**
     * Load WebLLM library dynamically
     * @returns {Promise<void>}
     */
    async loadWebLLMLibrary() {
        // WebLLM uses ES modules, so we need to use dynamic import() instead of script tag
        // Try different import paths as WebLLM package structure may vary
        const importPaths = [
            'https://unpkg.com/@mlc-ai/web-llm@0.2.80/lib/index.js',
            'https://cdn.jsdelivr.net/npm/@mlc-ai/web-llm@0.2.80/lib/index.js',
            'https://unpkg.com/@mlc-ai/web-llm@latest/lib/index.js',
            'https://cdn.jsdelivr.net/npm/@mlc-ai/web-llm@latest/lib/index.js'
        ];
        
        let lastError = null;
        for (const importPath of importPaths) {
            try {
                console.log(`Attempting to load WebLLM from ${importPath}...`);
                const webllmModule = await import(importPath);
                // Store the module for later use - handle both default and named exports
                // WebLLM exports CreateMLCEngine directly, not as default
                if (webllmModule.CreateMLCEngine) {
                    this.webllmModule = webllmModule;
                } else if (webllmModule.default?.CreateMLCEngine) {
                    this.webllmModule = webllmModule.default;
                } else {
                    this.webllmModule = webllmModule.default || webllmModule;
                }
                console.log('WebLLM loaded successfully from', importPath);
                console.log('WebLLM module keys:', Object.keys(this.webllmModule));
                if (this.webllmModule.CreateMLCEngine) {
                    console.log('CreateMLCEngine found in module');
                } else {
                    console.warn('CreateMLCEngine not found in module');
                }
                return;
            } catch (error) {
                console.error(`Failed to load WebLLM from ${importPath}:`, error);
                lastError = error;
                continue;
            }
        }
        
        // If all paths failed, throw error with details
        throw new Error(`Failed to load WebLLM library from all CDN sources. Last error: ${lastError?.message || 'Unknown error'}. Browser AI requires an internet connection. Please check your network connection and try again.`);
    },
    
    /**
     * Try loading WebLLM from jsdelivr CDN as fallback
     * @param {Function} resolve
     * @param {Function} reject
     */
    tryJsdelivrCDN(resolve, reject) {
        const altScript = document.createElement('script');
        altScript.type = 'module'; // WebLLM uses ES modules
        // Try jsdelivr with correct path
        altScript.src = 'https://cdn.jsdelivr.net/npm/@mlc-ai/web-llm@0.2.80/lib/index.js';
        altScript.onload = () => {
            // Check for both webllm and WebLLM (different naming conventions)
            if (typeof webllm !== 'undefined' || typeof WebLLM !== 'undefined' || window.webllm || window.WebLLM) {
                resolve();
            } else {
                reject(new Error('WebLLM library loaded but webllm object not available. Browser AI is not supported in this environment. Please check browser console for details.'));
            }
        };
        altScript.onerror = () => {
            reject(new Error('Failed to load WebLLM library from both CDN sources. Browser AI requires an internet connection. Please check your network connection and try again.'));
        };
        document.head.appendChild(altScript);
    },
    
    /**
     * Load TensorFlow.js library dynamically
     * @returns {Promise<void>}
     */
    async loadTensorFlowLibrary() {
        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/@tensorflow/tfjs@latest/dist/tf.min.js';
            script.onload = () => resolve();
            script.onerror = () => reject(new Error('Failed to load TensorFlow.js library'));
            document.head.appendChild(script);
        });
    },
    
    /**
     * Generate text using browser AI
     * @param {string} prompt Input prompt
     * @param {Object} options Generation options
     * @returns {Promise<string>} Generated text
     */
    async generateText(prompt, options = {}) {
        try {
            if (!this.currentModel) {
                throw new Error('Browser AI not initialized. Call initBrowserAI() first.');
            }
            
            if (this.currentModelType === 'webllm') {
                return await this.generateWithWebLLM(prompt, options);
            } else if (this.currentModelType === 'tensorflow') {
                return await this.generateWithTensorFlow(prompt, options);
            } else {
                throw new Error(`Unknown model type: ${this.currentModelType}`);
            }
        } catch (error) {
            console.error('Error generating text:', error);
            throw error;
        }
    },
    
    /**
     * Generate text with WebLLM
     * @param {string} prompt Input prompt
     * @param {Object} options Generation options
     * @returns {Promise<string>} Generated text
     */
    async generateWithWebLLM(prompt, options = {}) {
        if (!this.webllmEngine) {
            throw new Error('WebLLM engine not initialized');
        }
        
        // WebLLM uses OpenAI-compatible chat.completions API
        // Convert prompt to messages format
        const messages = [
            { role: 'user', content: prompt }
        ];
        
        const generationConfig = {
            messages: messages,
            temperature: options.temperature || 0.7,
            max_tokens: options.maxTokens || 2000,
            stop: options.stop || [],
        };
        
        // Try OpenAI-compatible API first, fallback to complete() if needed
        let response;
        if (this.webllmEngine.chat && this.webllmEngine.chat.completions && this.webllmEngine.chat.completions.create) {
            response = await this.webllmEngine.chat.completions.create(generationConfig);
            // Extract text from OpenAI-compatible response
            return response.choices[0]?.message?.content || response.choices[0]?.text || '';
        } else if (this.webllmEngine.complete) {
            // Fallback to older API
            response = await this.webllmEngine.complete(prompt, {
                temperature: generationConfig.temperature,
                maxTokens: generationConfig.max_tokens,
                stop: generationConfig.stop
            });
            return typeof response === 'string' ? response : (response.text || response.content || '');
        } else {
            throw new Error('WebLLM engine does not support chat.completions or complete methods');
        }
    },
    
    /**
     * Generate text with TensorFlow.js (simplified - would need actual model)
     * @param {string} prompt Input prompt
     * @param {Object} options Generation options
     * @returns {Promise<string>} Generated text
     */
    async generateWithTensorFlow(prompt, options = {}) {
        // Placeholder - actual implementation would use TensorFlow.js model
        // For now, return a simple response
        throw new Error('TensorFlow.js generation not yet implemented. Please use WebLLM instead.');
    },
    
    /**
     * Check if browser supports WebGPU/WebGL for AI execution
     * @returns {Object} Support information
     */
    checkBrowserSupport() {
        const support = {
            webgpu: false,
            webgl: false,
            indexeddb: false,
            required: false,
        };
        
        // Check WebGPU
        if (navigator.gpu) {
            support.webgpu = true;
            support.required = true;
        }
        
        // Check WebGL as fallback
        const canvas = document.createElement('canvas');
        const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');
        if (gl) {
            support.webgl = true;
            if (!support.webgpu) {
                support.required = true;
            }
        }
        
        // Check IndexedDB
        if (window.indexedDB) {
            support.indexeddb = true;
        }
        
        return support;
    },
    
    /**
     * Get TensorFlow.js model URL (placeholder)
     * @param {string} modelName Model name
     * @returns {string} Model URL
     */
    getTensorFlowModelUrl(modelName) {
        // Placeholder - would need actual model URLs
        return `https://example.com/models/${modelName}/model.json`;
    },
    
    /**
     * Estimate model size in bytes
     * @param {string} modelName Model name
     * @param {string} modelType Model type
     * @returns {number} Estimated size in bytes
     */
    estimateModelSize(modelName, modelType = 'webllm') {
        // Rough estimates in bytes
        const sizeMap = {
            webllm: {
                'llama3.2': 2 * 1024 * 1024 * 1024, // ~2GB
                'mistral': 4 * 1024 * 1024 * 1024, // ~4GB
                'phi3': 2 * 1024 * 1024 * 1024, // ~2GB
            },
            tensorflow: {
                'universal-sentence-encoder': 100 * 1024 * 1024, // ~100MB
            }
        };
        
        return sizeMap[modelType]?.[modelName] || 500 * 1024 * 1024; // Default 500MB
    },
    
    /**
     * Cleanup and release resources
     */
    async cleanup() {
        if (this.webllmEngine) {
            // Release WebLLM resources
            this.webllmEngine = null;
        }
        
        if (this.tensorflowModel) {
            // Dispose TensorFlow.js model
            this.tensorflowModel.dispose();
            this.tensorflowModel = null;
        }
        
        this.currentModel = null;
        this.currentModelType = null;
    }
};

