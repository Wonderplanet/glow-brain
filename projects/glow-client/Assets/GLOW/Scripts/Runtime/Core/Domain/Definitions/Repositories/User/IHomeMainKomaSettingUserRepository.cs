using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.Models;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Core.Domain.Repositories
{
    public interface IHomeMainKomaSettingUserRepository
    {
        bool IsLoaded { get; }
        void Load();

        void SaveUnit(
            MasterDataId targetMstHomeMainKomaPatternId,
            HomeMainKomaUnitAssetSetPlaceIndex targetUnitAssetSetPlaceIndex,
            MasterDataId targetMstUnitId);
        void Save(UserHomeMainKomaPatternModel userHomeMainKomaPatternModel);
        IReadOnlyList<UserHomeMainKomaPatternModel> GetAll();

        // HomeMainView
        MasterDataId CurrentMstHomeKomaPatternId { get; }
        void SetCurrentMstHomeKomaPatternId(MasterDataId mstHomeKomaPatternId);
        IReadOnlyDictionary<HomeMainKomaUnitAssetSetPlaceIndex, MasterDataId> GetCurrentHomeKomaMstUnitIds();

    }
}
