using System.Collections.Generic;
using System.Linq;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data.User;
using GLOW.Core.Data.DataStores;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Core.Extensions;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;
using Zenject;

namespace GLOW.Core.Data.Repositories
{
    public class HomeMainKomaSettingUserRepository :
        IHomeMainKomaSettingUserRepository,
        IHomeMainKomaSettingFilterCacheRepository
    {
        [Inject] IUserHomeKomaSettingDataLocalDataStore DataStore { get; }
        bool IHomeMainKomaSettingUserRepository.IsLoaded => DataStore.IsLoaded;
        IReadOnlyList<MasterDataId> _cachedFilterMstSeriesIds = new List<MasterDataId>();

        MasterDataId IHomeMainKomaSettingUserRepository.CurrentMstHomeKomaPatternId =>
            DataStore.CurrentMstHomeKomaPatternId;

        IReadOnlyList<MasterDataId> IHomeMainKomaSettingFilterCacheRepository.CachedFilterMstSeriesIds =>
             _cachedFilterMstSeriesIds;


        void IHomeMainKomaSettingUserRepository.Load()
        {
            DataStore.Load();
        }

        void IHomeMainKomaSettingUserRepository.SaveUnit(
            MasterDataId targetMstHomeMainKomaPatternId,
            HomeMainKomaUnitAssetSetPlaceIndex targetUnitAssetSetPlaceIndex,
            MasterDataId targetMstUnitId)
        {
            // 保存するUnitDataを作成
            var unitData = new UserHomeKomaUnitSettingData(
                targetMstUnitId,
                targetUnitAssetSetPlaceIndex
            );
            // 更新後のUserHomeKomaSettingDataを作成
            var newUserPropertyData = CreateUpdatedData(
                targetMstHomeMainKomaPatternId,
                unitData
            );

            // 保存
            DataStore.Save(newUserPropertyData);
        }

        UserHomeKomaSettingData CreateUpdatedData(
            MasterDataId targetMstHomeMainKomaPatternId,
            UserHomeKomaUnitSettingData newUnitSettingData)
        {
            var userPropertyData = DataStore.GetAll();

            // MstKomaPatternIdから親要素Dataを取得
            var targetData = userPropertyData
                .FirstOrDefault(d => d.MstHomeKomaPatternId == targetMstHomeMainKomaPatternId);
            if (targetData == null)
            {
                // 該当データが無ければ新規作成
                return new UserHomeKomaSettingData(
                    targetMstHomeMainKomaPatternId,
                    new List<UserHomeKomaUnitSettingData>()
                    {
                        newUnitSettingData
                    }
                );
            }

            // 子要素であるUnitsから同じIndexを持つDataを置き換えまたは追加
            var newUnitSettingDataList = targetData.UserHomeKomaUnitSettingDatas
                .ReplaceOrAdd(
                    u => u.PlaceIndex == newUnitSettingData.PlaceIndex,
                    newUnitSettingData);

            return new UserHomeKomaSettingData(
                targetData.MstHomeKomaPatternId,
                newUnitSettingDataList
            );
        }

        void IHomeMainKomaSettingUserRepository.Save(UserHomeMainKomaPatternModel userHomeMainKomaPatternModel)
        {
            var data = new UserHomeKomaSettingData(
                userHomeMainKomaPatternModel.MstKomaPatternId,
                userHomeMainKomaPatternModel.UserHomeKomaUnitSettingModels
                    .Select(m =>
                    {
                        return new UserHomeKomaUnitSettingData(
                            m.MstUnitId,
                            m.PlaceIndex
                        );
                    }).ToList()
            );
            DataStore.Save(data);
        }

        IReadOnlyList<UserHomeMainKomaPatternModel> IHomeMainKomaSettingUserRepository.GetAll()
        {
            var userPropertyData = DataStore.GetAll();
            return userPropertyData
                .Select(CreateUserHomeMainKomaPatternModel)
                .ToList();
        }

        UserHomeMainKomaPatternModel CreateUserHomeMainKomaPatternModel(UserHomeKomaSettingData data)
        {
            return new UserHomeMainKomaPatternModel(
                data.MstHomeKomaPatternId,
                data.UserHomeKomaUnitSettingDatas
                    .Select(s =>
                    {
                        return new UserHomeKomaUnitSettingModel(
                            s.MstUnitId,
                            s.PlaceIndex
                        );
                    }).ToList()
            );
        }

        void IHomeMainKomaSettingUserRepository.SetCurrentMstHomeKomaPatternId(MasterDataId mstHomeKomaPatternId)
        {
            DataStore.SetCurrentMstHomeKomaPatternId(mstHomeKomaPatternId);
        }

        IReadOnlyDictionary<HomeMainKomaUnitAssetSetPlaceIndex, MasterDataId>
            IHomeMainKomaSettingUserRepository.GetCurrentHomeKomaMstUnitIds()
        {
            return DataStore.GetCurrentHomeKomaMstUnitIds();
        }

        void IHomeMainKomaSettingFilterCacheRepository.UpdateFilterMstSeriesIds(IReadOnlyList<MasterDataId> filterMstSeriesIds)
        {
            _cachedFilterMstSeriesIds = filterMstSeriesIds;
        }
    }
}
