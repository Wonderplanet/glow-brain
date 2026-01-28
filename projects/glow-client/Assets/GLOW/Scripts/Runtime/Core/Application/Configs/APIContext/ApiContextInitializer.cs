using Cysharp.Text;
using GLOW.Core.Domain.Modules.Network;
using GLOW.Core.Domain.Modules.Serializers;
using UnityHTTPLibrary;
using UnityHTTPLibrary.UnityWebRequestImpl;
using WPFramework.Modules.Benchmark;
using WPFramework.Modules.Log;
using WPFramework.Modules.Observability;
using Zenject;

namespace GLOW.Core.Application.Configs.APIContext
{
    public class ApiContextInitializer : IApiContextInitializer
    {
        [Inject] IServerErrorDelegate ServerErrorDelegate { get; }
        [Inject] ITimeOutDelegate TimeOutDelegate { get; }
        [Inject] IEncryptionSettings ApiEncryptionSettings { get; }
        [Inject] IHttpRequestFactoryCreator HttpRequestFactoryCreator { get; }
        [InjectOptional] ITLSCertificateHandler CertificateHandler { get; }

        void IApiContextInitializer.Initialize(ServerApi context, ApiContextInitializeSettings contextInitializeSettings)
        {
            Configure(context, contextInitializeSettings);
        }

        void Configure(ServerApi context, ApiContextInitializeSettings contextInitializeSettings)
        {
            // NOTE: エラー通知設定
            context.ServerErrorDelegate = contextInitializeSettings.NeedsErrorHandling ? ServerErrorDelegate : null;
            // NOTE: タイムアウト通知設定
            context.TimeOutDelegate = TimeOutDelegate;
            // NOTE: リクエスト設定
            context.HTTPRequestFactory = HttpRequestFactoryCreator.Create(CertificateHandler);
            // NOTE: イベント通知設定
            //       UnityWebRequestの場合はObservabilityKitが自動的に収集しているので設定しない
            var isUnityWebRequest = IsUnityWebRequestFactory(context.HTTPRequestFactory);
            context.RequestTaskEventDelegate = isUnityWebRequest ? null : new ObservabilityKitHttpTaskEventDelegate();
            
            if (isUnityWebRequest)
            {
                var logMessage = ZString.Format(
                    "Use {0}. ObservabilityKitHttpTaskEventDelegate is not used.",
                    nameof(ObservabilityKitHttpTaskEventDelegate));
                
                ApplicationLog.Log(nameof(ApiContextInitializer), logMessage);
            }

            // NOTE: タイムアウト時間設定
            context.RequestTimeout = contextInitializeSettings.RequestTimeout;

            // NOTE: Deserializerの設定
            context.DeserializerRegistry.Set(MimeTypes.Json, new CustomHttpJsonDotNetSerializer());

            if (contextInitializeSettings.UseEncryption)
            {
                context.EncryptionSettings = ApiEncryptionSettings;
            }
        }

        static bool IsUnityWebRequestFactory(IHTTPRequestFactory factory)
        {
#if OBSERVABILITY_DATADOG_ENABLED
            return factory is UnityWebRequestFactory || factory is DatadogTrackedWebRequestFactory;
#else
            return factory is UnityWebRequestFactory;
#endif
        }
    }
}
