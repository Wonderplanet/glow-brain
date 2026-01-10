using WonderPlanet.AnalyticsBridge;
using WonderPlanet.AnalyticsBridge.AnalyticsFirebase;
using WonderPlanet.AnalyticsBridge.Adjust;
using WonderPlanet.CrashReporterBridge;
using WonderPlanet.CrashReporterBridge.FirebaseCrashlytics;
using WonderPlanet.ObservabilityKit;
using WonderPlanet.RemoteNotificationBridge;
using WonderPlanet.RemoteNotificationBridge.FirebaseCludMessaging;
using WondlerPlanet.LocalNotification;
using Zenject;

namespace WPFramework.Application.Installers
{
    internal sealed class SDKInstaller : Installer
    {
        public override void InstallBindings()
        {
            // NOTE: ローカルプッシュライブラリ
            Container.BindInterfacesTo<LocalNotificationCenter>().AsCached();

            // NOTE: 分析基盤ライブラリ
            Container.Bind<AnalyticsCenter>()
                .FromInstance(new AnalyticsCenter(new IAnalyticsAgent[]
                {
                    new FirebaseAgent(),
                    new AdjustAgent()
                }))
                .AsCached();

            // NOTE: クラッシュレポート基盤ライブラリ
            Container.Bind<CrashReportCenter>()
                .FromInstance(new CrashReportCenter(new ICrashReportAgent[]
                {
                    // NOTE: FirebaseAnalyticsはException全てをハンドリングして送信するためFirebaseAnalyticsに通知されたくないものは
                    //       全て事前にハンドリングする必要がある
                    new FirebaseCrashlyticsAgent()
                }))
                .AsCached();

            // NOTE: リモートプッシュ通知ライブラリ
            Container.Bind<RemoteNotificationCenter>()
                .FromInstance(new RemoteNotificationCenter(new IRemoteNotificationAgent[]
                {
                    new FirebaseCloudMessagingNotificationAgent()
                }))
                .AsCached();

            // NOTE: ObservabilityKitが利用するレポーターをインストール
            Container.BindInterfacesTo<DatadogReporter>().AsCached();
        }
    }
}
