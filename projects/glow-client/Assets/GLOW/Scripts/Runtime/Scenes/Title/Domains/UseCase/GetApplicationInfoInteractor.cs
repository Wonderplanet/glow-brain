using GLOW.Core.Domain.Repositories;
using GLOW.Scenes.Title.Domains.Model;
using GLOW.Scenes.Title.Domains.ValueObjects;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public class GetApplicationInfoInteractor : IGetApplicationInfoUseCase
    {
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IPreferenceRepository PreferenceRepository { get; }
        
        public ApplicationInfoModel GetApplicationInformation()
        {
            var applicationInfo = SystemInfoProvider.GetApplicationSystemInfo();
            var myId = PreferenceRepository.UserMyId;
            return new ApplicationInfoModel(
                new ApplicationVersion(applicationInfo.Version),
                myId);
        }
    }
}
