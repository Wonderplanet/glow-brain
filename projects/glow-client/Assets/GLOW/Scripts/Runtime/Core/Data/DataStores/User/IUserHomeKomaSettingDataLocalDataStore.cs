using System.Collections.Generic;
using System.Threading;
using Cysharp.Threading.Tasks;
using GLOW.Core.Data.Data.User;
using GLOW.Core.Domain.ValueObjects;
using GLOW.Scenes.Home.Domain.ValueObjects;

namespace GLOW.Core.Data.DataStores
{
    public interface IUserHomeKomaSettingDataLocalDataStore
    {
        bool IsLoaded { get; }
        void Load();
        void Save(UserHomeKomaSettingData userHomeKomaSettingData);
        void Delete();
        IReadOnlyList<UserHomeKomaSettingData> GetAll();

        // HomeMainView
        MasterDataId CurrentMstHomeKomaPatternId { get; }
        void SetCurrentMstHomeKomaPatternId(MasterDataId mstHomeKomaPatternId);

        IReadOnlyDictionary<HomeMainKomaUnitAssetSetPlaceIndex, MasterDataId>
            GetCurrentHomeKomaMstUnitIds();

    }
}
