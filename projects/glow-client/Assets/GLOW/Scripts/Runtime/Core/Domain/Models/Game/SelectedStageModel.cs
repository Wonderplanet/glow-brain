using GLOW.Core.Domain.Constants;
using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Core.Domain.Models
{
    public enum InGameType
    {
        Normal,
        AdventBattle,
        Pvp
    }
    public record SelectedStageModel(
        MasterDataId SelectedStageId,
        MasterDataId SelectedMstAdventBattleId,
        ContentSeasonSystemId SelectedSysPvpSeasonId)
    {
        public static SelectedStageModel Empty { get; } = new SelectedStageModel(
            MasterDataId.Empty,
            MasterDataId.Empty,
            ContentSeasonSystemId.Empty);

        public InGameType InGameType =>
            !SelectedMstAdventBattleId.IsEmpty() ? InGameType.AdventBattle :
            !SelectedSysPvpSeasonId.IsEmpty() ? InGameType.Pvp :
            InGameType.Normal;

        public InGameContentType InGameContentType =>
            !SelectedMstAdventBattleId.IsEmpty() ? InGameContentType.AdventBattle :
            !SelectedSysPvpSeasonId.IsEmpty() ? InGameContentType.Pvp :
            InGameContentType.Stage;

        public MasterDataId SelectedId =>
            !SelectedMstAdventBattleId.IsEmpty() ? SelectedMstAdventBattleId :
            !SelectedSysPvpSeasonId.IsEmpty() ? SelectedSysPvpSeasonId.ToMasterDataId() :
            SelectedStageId;
    }
}
