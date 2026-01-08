using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.Models.Announcement;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using WPFramework.Constants.Platform;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Domain.UseCase
{
    public class CheckAllAnnouncementReadUseCase
    {
        [Inject] IAnnouncementService AnnouncementService { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IAnnouncementCacheRepository AnnouncementCacheRepository { get; }
        [Inject] IAnnouncementPreferenceRepository AnnouncementPreferenceRepository { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }

        public async UniTask<AlreadyReadAnnouncementFlag> GetAllAnnouncementAlreadyRead(CancellationToken cancellationToken)
        {
            var result = await AnnouncementService.LastUpdated(cancellationToken, TimeProvider.Now);
            
            var lastUpdatedTimes = GetAnnouncementLastUpdateAtByOs(result);
            AnnouncementCacheRepository.SetInformationLastUpdated(lastUpdatedTimes.InformationLastUpdated);
            AnnouncementCacheRepository.SetOperationLastUpdated(lastUpdatedTimes.OperationLastUpdated);
            
            var preferenceInformationLastUpdated = AnnouncementPreferenceRepository.ReadInformationLastUpdated;
            var preferenceOperationLastUpdated = AnnouncementPreferenceRepository.ReadOperationLastUpdated;
            
            // OSごとの値で判定するよう修正
            if (preferenceInformationLastUpdated < lastUpdatedTimes.InformationLastUpdated || 
                preferenceOperationLastUpdated < lastUpdatedTimes.OperationLastUpdated)
            {
                return AlreadyReadAnnouncementFlag.False;
            }
            
            return AlreadyReadAnnouncementFlag.True;
        }

        (AnnouncementLastUpdateAt InformationLastUpdated, AnnouncementLastUpdateAt OperationLastUpdated) GetAnnouncementLastUpdateAtByOs(
            AnnouncementLastUpdatedModel model)
        {
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            if (platformId == PlatformId.Android)
            {
                return (model.InformationAndroid, model.OperationAndroid);
            }
            else if (platformId == PlatformId.IOS)
            {
                return (model.InformationIos, model.OperationIos);
            }
            
            return (AnnouncementLastUpdateAt.Empty, AnnouncementLastUpdateAt.Empty);
        }
    }
}
