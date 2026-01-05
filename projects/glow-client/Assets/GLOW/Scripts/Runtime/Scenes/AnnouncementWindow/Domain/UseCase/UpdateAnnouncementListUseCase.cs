using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.Models.Announcement;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.Services;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Applier;
using GLOW.Scenes.AnnouncementWindow.Domain.Enum;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;
using WPFramework.Constants.Platform;
using WPFramework.Domain.Modules;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Domain.UseCase
{
    public class UpdateAnnouncementListUseCase
    {
        [Inject] IAnnouncementService AnnouncementService { get; }
        [Inject] ITimeProvider TimeProvider { get; }
        [Inject] IAnnouncementPreferenceRepository AnnouncementPreferenceRepository { get; }
        [Inject] IAnnouncementCacheRepository AnnouncementCacheRepository { get; }
        [Inject] IAnnouncementDateTimeApplier AnnouncementDateTimeApplier { get; }
        [Inject] ISystemInfoProvider SystemInfoProvider { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }

        public async UniTask<FetchAnnouncementListModel> UpdateAndGetAnnouncementList(
            CancellationToken cancellationToken,
            AnnouncementDisplayMeansType displayMeansType)
        {
            var result = await AnnouncementService.Index(cancellationToken, TimeProvider.Now);
            var filteredAnnouncementModels = FilterAnnouncementsByOs(result.AnnouncementModels);
            
            var readAnnouncementDictionary =  filteredAnnouncementModels
                .Select(CreateAnnouncementCellUseCaseModel)
                .ToDictionary(model => model.AnnouncementId, model => model);

            // お知らせで取得したものについてIDを保存
            AnnouncementCacheRepository.SetReadAnnouncementDictionary(readAnnouncementDictionary);
            
            // 表示する時に情報を保存する
            AnnouncementDateTimeApplier.UpdateAnnouncementLastUpdatedDateTimeAtLogin(
                TimeProvider.Now,
                AnnouncementCacheRepository.GetInformationLastUpdated(),
                AnnouncementCacheRepository.GetOperationLastUpdated(),
                displayMeansType);
            
            // 更新が入った既読お知らせについては保存されている情報を削除する(副作用)
            RemoveReadAnnouncementIdAndLastUpdated(readAnnouncementDictionary);
            
            var hookedPatternUrl = MstConfigRepository.GetConfig(
                MstConfigKey.AnnouncementHookedPatternUrl).Value.ToHookedPatternUrl();
            
            var model = new FetchAnnouncementListModel(
                readAnnouncementDictionary.Values.ToList(),
                hookedPatternUrl);
            return model;
        }

        AnnouncementCellUseCaseModel CreateAnnouncementCellUseCaseModel(AnnouncementModel announcementModel)
        {
            var cellType = announcementModel.BannerUrl.IsEmpty() ? AnnouncementCellType.Text : AnnouncementCellType.Banner;
            var tabType =
                (announcementModel.AnnouncementCategory == AnnouncementCategory.Bug ||
                 announcementModel.AnnouncementCategory == AnnouncementCategory.Important)
                    ? AnnouncementTabType.Operation
                    : AnnouncementTabType.Event;

            var readAnnouncementIdAndLastUpdated = AnnouncementPreferenceRepository.ReadAnnouncementIdAndLastUpdated;
            if (readAnnouncementIdAndLastUpdated.TryGetValue(announcementModel.AnnouncementId, out var lastUpdatedAt))
            {
                // 最終更新日と一致&既にIDが記録されていたら既読
                var isRead = announcementModel.GetLastUpdateAt() == lastUpdatedAt;
                return new AnnouncementCellUseCaseModel(
                    announcementModel.AnnouncementId,
                    tabType,
                    cellType,
                    announcementModel.AnnouncementCategory,
                    announcementModel.LastUpdatedAt,
                    announcementModel.Title,
                    announcementModel.BannerUrl,
                    announcementModel.Status,
                    announcementModel.ContentsUrl,
                    announcementModel.StartAt,
                    announcementModel.EndAt,
                    isRead
                );
            }
            else
            {
                // 記録されていない場合は未読
                return new AnnouncementCellUseCaseModel(
                    announcementModel.AnnouncementId,
                    tabType,
                    cellType,
                    announcementModel.AnnouncementCategory,
                    announcementModel.LastUpdatedAt,
                    announcementModel.Title,
                    announcementModel.BannerUrl,
                    announcementModel.Status,
                    announcementModel.ContentsUrl,
                    announcementModel.StartAt,
                    announcementModel.EndAt,
                    false
                );
            }
        }

        IReadOnlyList<AnnouncementModel> FilterAnnouncementsByOs(IReadOnlyList<AnnouncementModel> models)
        {
            var platformId = SystemInfoProvider.GetApplicationSystemInfo().PlatformId;
            if( platformId == PlatformId.Android)
            {
                // Androidの場合はAndroid向けのお知らせのみを取得
                return models.Where(
                        model => model.AnnouncementOsType.IsAll() || model.AnnouncementOsType.IsAndroid())
                    .ToList();
            }
            else if (platformId == PlatformId.IOS)
            {
                // iOSの場合はiOS向けのお知らせのみを取得
                return models.Where(
                        model => model.AnnouncementOsType.IsAll() || model.AnnouncementOsType.IsIos())
                    .ToList();
            }
            
            return models;
        }

        void RemoveReadAnnouncementIdAndLastUpdated(IReadOnlyDictionary<AnnouncementId, AnnouncementCellUseCaseModel> readAnnouncementDictionary)
        {
            // 未読のお知らせIDリストを取得(内容の更新があった場合は未読扱いにする)
            var announcementIdsToRemoveFromRead = readAnnouncementDictionary
                .Where(pair => !pair.Value.IsRead)
                .Select(pair => pair.Key)
                .ToList();
            
            // 既に端末保存されているお知らせ情報から、内容が更新されて未読扱いにしたお知らせの情報を削除する(副作用)
            AnnouncementPreferenceRepository.RemoveReadAnnouncementIdAndLastUpdated(announcementIdsToRemoveFromRead);
        }
    }
}
