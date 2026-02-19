using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.AnnouncementWindow;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.Model;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Domain.UseCase
{
    public class GetCachedAnnouncementListUseCase
    {
        [Inject] IAnnouncementCacheRepository AnnouncementCacheRepository { get; }
        [Inject] IMstConfigRepository MstConfigRepository { get; }
        public FetchAnnouncementListModel GetCacheInformationList(
            AnnouncementTabType tabType,
            IReadOnlyList<AnnouncementId> readAnnouncementIds)
        {
            var cache =  AnnouncementCacheRepository.GetReadAnnouncementList();
            
            // 処理の前に重複を外す
            var readAnnouncementUniqueIds = readAnnouncementIds.ToHashSet();
            var updatedUseCaseModels = cache
                .Select(model =>
                {
                    if (model.AnnouncementTabType == tabType)
                    {
                        return model with
                        {
                            IsRead = model.IsRead || readAnnouncementUniqueIds.Contains(model.AnnouncementId),
                        };
                    }
                    
                    return model;
                })
                .ToList();
            
            var readAnnouncementDictionary = updatedUseCaseModels
                .ToDictionary(model => model.AnnouncementId, model => model);
            AnnouncementCacheRepository.SetReadAnnouncementDictionary(readAnnouncementDictionary);
            
            var hookedPatternUrl = MstConfigRepository.GetConfig(
                MstConfigKey.AnnouncementHookedPatternUrl).Value.ToHookedPatternUrl();
            
            return new FetchAnnouncementListModel(updatedUseCaseModels,
                hookedPatternUrl);
        }
    }
}