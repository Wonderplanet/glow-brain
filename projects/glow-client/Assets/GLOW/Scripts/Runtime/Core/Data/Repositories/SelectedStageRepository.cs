using GLOW.Core.Domain.Models;
using GLOW.Core.Domain.Repositories;
using GLOW.Core.Domain.ValueObjects;
using WondlerPlanet.CheatProtectKit.ObscuredTypes;

namespace GLOW.Core.Data.Repositories
{
    public class SelectedStageRepository : ISelectedStageRepository
    {
        ObscuredString _selectedStageId;
        ObscuredString _selectedMstAdventBattleId;
        ObscuredString _selectedSysPvpSeasonId;

        public void Save(SelectedStageModel selectedStageModel)
        {
            _selectedStageId = selectedStageModel.SelectedStageId.Value;
            _selectedMstAdventBattleId = selectedStageModel.SelectedMstAdventBattleId.Value;
            _selectedSysPvpSeasonId = selectedStageModel.SelectedSysPvpSeasonId.Value;
        }

        public SelectedStageModel Get()
        {
            var mstStageId = string.IsNullOrEmpty(_selectedStageId)
                ? MasterDataId.Empty
                : new MasterDataId(_selectedStageId);
            var mstAdventBattleId = string.IsNullOrEmpty(_selectedMstAdventBattleId)
                ? MasterDataId.Empty
                : new MasterDataId(_selectedMstAdventBattleId);
            var sysPvpSeasonId = string.IsNullOrEmpty(_selectedSysPvpSeasonId)
                ? ContentSeasonSystemId.Empty
                : new ContentSeasonSystemId(_selectedSysPvpSeasonId);

            return new SelectedStageModel(mstStageId, mstAdventBattleId, sysPvpSeasonId);
        }
    }
}
