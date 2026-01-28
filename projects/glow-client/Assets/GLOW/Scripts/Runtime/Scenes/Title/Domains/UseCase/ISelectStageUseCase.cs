using GLOW.Core.Domain.ValueObjects;

namespace GLOW.Scenes.Title.Domains.UseCase
{
    public interface ISelectStageUseCase
    {
        void SelectStage(MasterDataId mstStageId, MasterDataId mstAdventBattleId, ContentSeasonSystemId sysPvpSeasonId);
    }
}
