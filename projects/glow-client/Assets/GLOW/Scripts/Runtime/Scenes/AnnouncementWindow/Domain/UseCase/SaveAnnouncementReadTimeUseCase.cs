using System.Collections.Generic;
using System.Linq;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects.AnnouncementWindow;
using GLOW.Scenes.AnnouncementWindow.Domain.ValueObject;
using WonderPlanet.UnityStandard.Extension;
using Zenject;

namespace GLOW.Scenes.AnnouncementWindow.Domain.UseCase
{
    public class SaveAnnouncementReadTimeUseCase
    {
        [Inject] IAnnouncementPreferenceRepository AnnouncementPreferenceRepository { get; }
        [Inject] IAnnouncementCacheRepository AnnouncementCacheRepository { get; }

        public AlreadyReadAnnouncementFlag SaveReadAnnouncement(
            IReadOnlyList<AnnouncementId> readInformationAnnouncementIds, 
            IReadOnlyList<AnnouncementId> readOperationAnnouncementIds)
        {
            var informationIdSet = readInformationAnnouncementIds.Distinct();
            var informationDictionary = informationIdSet.ToDictionary(
                id => id, 
                id => AnnouncementCacheRepository.Get(id).GetLastUpdateAt());
            
            var operationIdSet = readOperationAnnouncementIds.Distinct();
            var operationDictionary = operationIdSet.ToDictionary(
                id => id, 
                id => AnnouncementCacheRepository.Get(id).GetLastUpdateAt());
            
            var dictionary = informationDictionary
                .Concat(operationDictionary.Where(pair =>
                    !informationDictionary.ContainsKey(pair.Key))
                ).ToDictionary(
                    pair => pair.Key,
                    pair => pair.Value
                );
            
            // 読んだお知らせ、タブ単位での最新更新お知らせを保存する
            var readAnnouncementIdAndLastUpdated = UpdateReadAnnouncement(dictionary);
            AnnouncementPreferenceRepository.SetReadAnnouncementIdAndLastUpdated(readAnnouncementIdAndLastUpdated);
            if (!informationDictionary.IsEmpty())
            {
                var readInformationLastUpdateAt = informationDictionary.Max(x => x.Value);
                var currentInformationLastUpdateAt = AnnouncementPreferenceRepository.ReadInformationLastUpdated;
                
                // 読んだお知らせで最も最新の更新時間で記録
                // 既に保存されている最終更新時間と比較して、より新しい方を保存する
                var savedInformationLastUpdateAt = AnnouncementLastUpdateAt.Max(
                    readInformationLastUpdateAt,
                    currentInformationLastUpdateAt);
                
                AnnouncementPreferenceRepository.SetReadInformationLastUpdated(savedInformationLastUpdateAt);
            }
            
            if (!operationDictionary.IsEmpty())
            {
                var readOperationLastUpdateAt = operationDictionary.Max(x => x.Value);
                var currentOperationLastUpdateAt = AnnouncementPreferenceRepository.ReadOperationLastUpdated;
                
                // 読んだお知らせで最も最新の更新時間で記録
                // 既に保存されている最終更新時間と比較して、より新しい方を保存する
                var savedOperationLastUpdateAt = AnnouncementLastUpdateAt.Max(
                    readOperationLastUpdateAt,
                    currentOperationLastUpdateAt);
                AnnouncementPreferenceRepository.SetReadOperationLastUpdated(savedOperationLastUpdateAt);
            }
            
            // 現在表示されているお知らせ一覧のIDリスト
            var currentDisplayAnnouncementIdList =  AnnouncementCacheRepository.GetReadAnnouncementList()
                .Select(model => model.AnnouncementId)
                .ToList();
            
            // 読んだお知らせの最終更新日時がAPIからの最終更新日時と同じかそれ以上か(同じになっていれば最新のお知らせが既読と判断できる)
            var readLatestUpdateInformationAnnouncement = AnnouncementPreferenceRepository.ReadInformationLastUpdated >= AnnouncementCacheRepository.GetInformationLastUpdated();
            var readLatestUpdateOperationAnnouncement = AnnouncementPreferenceRepository.ReadOperationLastUpdated >= AnnouncementCacheRepository.GetOperationLastUpdated();

            // 全てのお知らせが既読済みかどうか
            var announcementAllReadFlag = new AlreadyReadAnnouncementFlag(
                currentDisplayAnnouncementIdList.All(id => readAnnouncementIdAndLastUpdated.ContainsKey(id))
                                                           && readLatestUpdateInformationAnnouncement 
                                                           && readLatestUpdateOperationAnnouncement);
            
            AnnouncementPreferenceRepository.SetAnnouncementAlreadyReadAll(announcementAllReadFlag);
            
            return announcementAllReadFlag;
        }
        
        Dictionary<AnnouncementId, AnnouncementLastUpdateAt> UpdateReadAnnouncement(
            Dictionary<AnnouncementId, AnnouncementLastUpdateAt> updatedReadAnnouncementIdAndLastUpdated)
        {
            var readAnnouncementIdAndLastUpdated = AnnouncementPreferenceRepository.ReadAnnouncementIdAndLastUpdated;
            foreach (var pair in updatedReadAnnouncementIdAndLastUpdated)
            {
                // 既に読んでいるお知らせの最終更新時間を更新
                readAnnouncementIdAndLastUpdated[pair.Key] = pair.Value;
            }

            return readAnnouncementIdAndLastUpdated;
        }
    }
}