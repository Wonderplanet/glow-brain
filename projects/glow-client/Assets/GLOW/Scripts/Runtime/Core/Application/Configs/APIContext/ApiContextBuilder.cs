using GLOW.Core.Domain.Modules.Network;
using UnityHTTPLibrary;
using UnityHTTPLibrary.HttpClientImpl;
using WPFramework.Constants.Zenject;
using Zenject;

namespace GLOW.Core.Application.Configs.APIContext
{
    public class ApiContextBuilder : IApiContextBuilder
    {
        [Inject(Id = FrameworkInjectId.ServerApi.Game)] ServerApi GameApiContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Asset)] ServerApi CdnContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.System)] ServerApi SystemApiContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Mst)] ServerApi MstContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Announcement)] ServerApi AnnouncementContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Banner)] ServerApi BannerContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Agreement)] ServerApi AgreementContext { get; }

        [Inject] IApiContextInitializer ApiContextInitializer { get; }

        void IApiContextBuilder.Build()
        {
            HttpClientProvider.Initialize();

            ApiContextInitializer.Initialize(GameApiContext, ApiContextInitializeSettings.Default);
            ApiContextInitializer.Initialize(CdnContext, ApiContextInitializeSettings.Asset);
            ApiContextInitializer.Initialize(SystemApiContext, ApiContextInitializeSettings.Default);
            ApiContextInitializer.Initialize(MstContext, ApiContextInitializeSettings.Asset);
            ApiContextInitializer.Initialize(AnnouncementContext, ApiContextInitializeSettings.Asset);
            ApiContextInitializer.Initialize(BannerContext, ApiContextInitializeSettings.Asset);
            ApiContextInitializer.Initialize(AgreementContext, ApiContextInitializeSettings.Agreement);

#if ENABLE_NETWORK_LOG
            ServerApiSharedConfig.LogEnable = true;
#else
            ServerApiSharedConfig.LogEnable = false;
#endif  // ENABLE_NETWORK_LOG
        }
    }
}
