using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Modules.Network;
using System.Threading;
using UnityHTTPLibrary;
using WPFramework.Constants.Zenject;
using Zenject;

namespace GLOW.Core.Application.Configs.APIContext
{
    public class ApiContextHeaderBuilder : IApiContextHeaderBuilder
    {
        [Inject(Id = FrameworkInjectId.ServerApi.Game)] ServerApi GameApiContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Asset)] ServerApi CdnContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.System)] ServerApi SystemApiContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Mst)] ServerApi MstContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Announcement)] ServerApi AnnouncementContext { get; }
        [Inject(Id = FrameworkInjectId.ServerApi.Banner)] ServerApi BannerContext { get; }
        [Inject] IApiContextHeaderModifier ApiContextHeaderModifier { get; }
        [Inject] IGameApiContextHeaderModifier GameApiContextHeaderModifier { get; }

        public async UniTask Build(CancellationToken cancellationToken)
        {
            await GameApiContextHeaderModifier.Configure(GameApiContext, cancellationToken);
            
            ApiContextHeaderModifier.Configure(SystemApiContext);
            ApiContextHeaderModifier.Configure(CdnContext);
            ApiContextHeaderModifier.Configure(MstContext);
            ApiContextHeaderModifier.Configure(AnnouncementContext);
            ApiContextHeaderModifier.Configure(BannerContext);
        }
    }
}

