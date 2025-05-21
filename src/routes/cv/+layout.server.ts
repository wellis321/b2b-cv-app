// This layout load function exists to ensure the CV public routes
// are properly handled without requiring authentication
export const load = async () => {
    // Simply return an empty object, allowing all routes under /cv
    // to be processed without requiring authentication
    return {};
};