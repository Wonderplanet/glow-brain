namespace GLOW.Core.Domain.Modules.Network
{
    public class ApiContextInitializeSettings
    {
        public int RequestTimeout { get; }
        public bool NeedsErrorHandling { get; }
        public bool UseEncryption { get; }

        public static ApiContextInitializeSettings Default { get; } =
            new ApiContextInitializeSettings(
                requestTimeout: ApiConfig.RequestTimeoutSeconds,
                needsErrorHandling: true,
                useEncryption: true);
        public static ApiContextInitializeSettings Unhandled { get; } =
            new ApiContextInitializeSettings(
                requestTimeout: ApiConfig.RequestTimeoutSeconds,
                needsErrorHandling: false,
                useEncryption: true);
        public static ApiContextInitializeSettings Asset { get; } =
            new ApiContextInitializeSettings(
                requestTimeout: ApiConfig.RequestTimeoutSeconds,
                needsErrorHandling: true,
                useEncryption: false);
        public static ApiContextInitializeSettings Agreement { get; } =
            new ApiContextInitializeSettings(
                requestTimeout: ApiConfig.RequestTimeoutSeconds,
                needsErrorHandling: true,
                useEncryption: false);

        public ApiContextInitializeSettings(int requestTimeout, bool needsErrorHandling, bool useEncryption)
        {
            RequestTimeout = requestTimeout;
            NeedsErrorHandling = needsErrorHandling;
            UseEncryption = useEncryption;
        }
    }
}